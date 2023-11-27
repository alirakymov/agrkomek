<?php

namespace Qore\EventManager;

/**
 * Class: Registry
 *
 */
class Registry
{
    /**
     * events
     *
     * @var mixed
     */
    private $events = [];

    /**
     * __construct
     *
     */
    public function __construct()
    {

    }

    /**
     * register
     *
     * @param string $_eventName
     */
    public function register($_eventName, $_listener)
    {
        $this->events[$_eventName] = array_merge(
            $this->events[$_eventName] ?? [],
            [$_listener]
        );
    }

    /**
     * getEvents
     *
     */
    public function getEvents()
    {
        return $this->events;
    }

}
