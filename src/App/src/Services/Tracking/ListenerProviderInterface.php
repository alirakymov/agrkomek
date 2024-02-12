<?php

namespace Qore\App\Services\Tracking;

interface ListenerProviderInterface
{
    /**
     * Subscribe to events in track
     *
     * @param TrackingInterface $_tracking 
     *
     * @return void
     */
    public function subscribe(TrackingInterface $_tracking): void;

}
