<?php

namespace Qore\App\SynapseNodes\Components\Tracker\Tracking;

use Closure;
use Qore\App\SynapseNodes\Components\TrackerPoint\TrackerPoint;

interface TrackerInterface
{
    /**
     * Initialize tracking - create tracker point
     *
     * @param TrackableInterface|Closure $_target
     * @param Closure|null $_closure
     *
     * @return TrackerInterface
     */
    public function track($_target, ?Closure $_closure = null): TrackerInterface;

    /**
     * Save data to current point
     *
     * @param  $_data (optional)
     *
     * @return TrackerInterface
     */
    public function save($_data = []): TrackerInterface;

    /**
     * Get current tracker point
     *
     * @return \Qore\App\SynapseNodes\Components\TrackerPoint\TrackerPoint
     */
    public function getPoint(): TrackerPoint;

}
