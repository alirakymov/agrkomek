<?php

namespace Qore\App\SynapseNodes\Components\Tracker\Tracking;

/**
 * Interface for consistent behavior of tracking subject
 * Tracker service will use the last unclosed (completed = null)
 * trackerPoint instance if exists otherwise create new;
 */
interface TrackableConsistentInterface extends TrackableInterface
{
    /**
     *  - true: Tracker service will close (completed = DateTime('now')) last unclosed and will create new
     *  TrackerPoint instance;
     *  - false: Tracker service will use the last unclosed (completed = null) if exists
     *  TrackerPoint instance or will create new;
     *
     * @return bool
     */
    public function alwaysCreateNew(): bool;

}
