<?php

namespace Qore\EventManager;

use Qore\Collection\Collection;
use Laminas\EventManager as ZendEventManager;

/**
 * Class: EventManager
 *
 * @see ZendEventManager\EventManager
 */
class EventManager extends ZendEventManager\EventManager
{
    /**
     * registries
     *
     * @var mixed
     */
    protected $registries = null;

    /**
     * __construct
     *
     * @param ZendEventManager\SharedEventManagerInterface $sharedEventManager
     * @param array $identifiers
     */
    public function __construct(ZendEventManager\SharedEventManagerInterface $_sharedEventManager = null, array $_identifiers = [])
    {
        parent::__construct($_sharedEventManager, $_identifiers);
        $this->registries = new Collection([]);
    }

    /**
     * Easy access for wrapWithRegistry
     *
     * @param callable $_callback 
     *
     * @return mixed 
     */
    public function __invoke(callable $_callback)
    {
        return $this->wrapWithRegistry($_callback);
    }

    /**
     * Each registry contains list of all events which registered after registration it.
     * This is necessary when you need to detach a group of events.
     *
     * @param Registry $_registry
     *
     * @return EventManager
     */
    public function setRegistry(Registry $_registry): EventManager
    {
        $this->registries = $this->registries->appendItem($_registry);
        return $this;
    }

    /**
     * Remove registry from list
     *
     * @param Registry $_registry
     *
     * @return EventManager
     */
    public function rejectRegistry(Registry $_registry): EventManager
    {
        $this->registries = $this->registries->reject(function($_emRegistry) use ($_registry) {
            return $_emRegistry === $_registry;
        });

        return $this;
    }

    /**
     * Attach event
     *
     * @param mixed $_eventName
     * @param callable $_listener
     * @param int $_priority
     */
    public function attach($_eventName, callable $_listener, $_priority = 1)
    {
        # - Register event name in all of registries
        if ($registry = $this->registries->last()) {
            $registry->register($_eventName, $_listener);
        }

        return parent::attach($_eventName, $_listener, $_priority);
    }

    /**
     * Detach each event from registry
     *
     * @param Registry $_registry 
     *
     * @return void 
     */
    public function detachRegistry(Registry $_registry): void
    {
        $events = $_registry->getEvents();
        foreach ($events as $eventName => $listeners) {
            foreach ($listeners as $listener) {
                $this->detach($listener, $eventName);
            }
        }
    }

    /**
     * Wrap callback logic with registry
     *
     * @param callable $_callback 
     *
     * @return mixed 
     */
    public function wrapWithRegistry(callable $_callback)
    {
        # - Initialize new registry
        $this->setRegistry($registry = new Registry());
        # - Execute callback with this registry
        $result = $_callback($this, $registry);
        # - Detach all events
        $this->detachRegistry($registry);
        # - Reject registry from this em
        $this->rejectRegistry($registry);

        return $result;
    }

    public function __clone()
    {
        if ($this->sharedManager) {
            $this->sharedManager = clone $this->sharedManager;
        }
    }

}
