<?php

namespace Qore\App\Services\Tracking;

use Closure;
use Laminas\EventManager\ResponseCollection;

interface TrackingInterface
{
    /**
     * Easy access for listen
     *
     * @param \Closure $_closure 
     *
     * @return mixed 
     */
    public function __invoke(Closure $_closure);

    /**
     * Initialize track
     *
     * @param \Closure $_closure 
     *
     * @return mixed 
     */
    public function track(Closure $_closure);

    /**
     * Register listener for event in the track
     *
     * @param string $_event 
     * @param \Closure $_listener 
     * @param int $_priority (optional)
     *
     * @return TrackingInterface 
     */
    public function listen(string $_event, Closure $_listener, int $_priority = 1): TrackingInterface;

    /**
     * Fire event
     *
     * @param string $_event 
     * @param  $_target 
     * @param array $_params (optional)
     *
     * @return \Laminas\EventManager\ResponseCollection
     */
    public function fire(string $_event, $_target, array $_params = []): ResponseCollection;

}
