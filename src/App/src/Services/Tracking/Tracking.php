<?php

namespace Qore\App\Services\Tracking;

use Closure;
use Laminas\EventManager\ResponseCollection;
use Qore\EventManager\EventManager;
use SplQueue;

class Tracking implements TrackingInterface
{
    /**
     * @var \Qore\EventManager\EventManager
     */
    private EventManager $_em;

    /**
     * @var bool - track is initialized 
     */
    private bool $initialized = false;

    /**
     * @var array
     */
    private array $_providers;

    /**
     * @var SplQueue postponed events 
     */
    private SplQueue $postponed;

    /**
     * Constructor
     *
     * @param \Qore\EventManager\EventManager $_em 
     * @param array $_providers 
     */
    public function __construct(EventManager $_em, array $_providers)
    {
        $this->_em = $_em;
        $this->_providers = $_providers;
        $this->postponed = new SplQueue();
    }

    /**
     * @inheritdoc
     */
    public function __invoke(Closure $_closure)
    {
        return $this->track($_closure);
    }

    /**
     * @inheritdoc
     */
    public function track(Closure $_closure)
    {
        $this->initialized = true;
        $result = $this->_em->wrapWithRegistry(function($_em) use ($_closure) {
            # - Initialize subscribes from listener providers
            foreach ($this->_providers as $provider) {
                $provider->subscribe($this);
            }
            # - return Closure result
            $result = $_closure($this, $_em);

            foreach ($this->postponed as $event) {
                $_em->trigger(...$event);
            }

            $this->postponed = new SplQueue();

            return $result;
        });
        $this->initialized = false;

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function listen(string $_event, Closure $_listener, int $_priority = 1): TrackingInterface
    {
        if (! $this->initialized) {
            $this->assertTrackIntialize();
        }

        $this->_em->attach($_event, $_listener, $_priority);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function fire(string $_event, $_target, array $_params = []): ResponseCollection
    {
        if (! $this->initialized) {
            $this->assertTrackIntialize();
        }

        return $this->_em->trigger($_event, $_target, $_params);
    }

    /**
     * @inheritdoc
     */
    public function postpone(string $_event, $_target, array $_params = []): void
    {
        if (! $this->initialized) {
            $this->assertTrackIntialize();
        }

        $this->postponed->enqueue([$_event, $_target, $_params]);
    }

    /**
     * Assert track initialization
     *
     * @throws TrackingException 
     *
     * @return void 
     */
    private function assertTrackIntialize(): void
    {
        // throw new TrackingException('Track is not initialized');
    }

}
