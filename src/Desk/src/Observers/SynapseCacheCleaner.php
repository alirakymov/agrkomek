<?php

declare(strict_types=1);

namespace Qore\Desk\Observers;

use Qore\Qore;
use Qore\CacheManager\CacheCleaner;
use Qore\Daemon\Supervisor\Supervisor;
use Qore\Daemon\Supervisor\SupervisorConfigurator;


/**
 * Class: ServiceFileCombiner
 *
 */
class SynapseCacheCleaner
{
    /**
     * __construct
     *
     */
    public function __construct()
    {
        # --
    }

    /**
     * afterSave
     *
     * @param mixed $_event
     */
    public function afterSave($_event)
    {
        # - Чистим cache
        // Qore::service(CacheCleaner::class)->clean();
    }

    /**
     * afterSave
     *
     * @param mixed $_event
     */
    public function afterDelete($_event)
    {
        # - Чистим cache
        // Qore::service(CacheCleaner::class)->clean();
    }

}
