<?php

namespace Qore\Core\Entities;

use Qore\Qore;
use Qore\Daemon\Supervisor\Supervisor;
use Qore\Daemon\Supervisor\SupervisorConfigurator;

/**
 * Class: QSystemService
 *
 * @see QSystemBase
 */
class QSystemService extends QSystemBase
{
    /**
     * name
     *
     */
    public function name()
    {
        return Qore::config('app.project-name') . '_' . $this->name;
    }

    /**
     * restart
     *
     */
    public function restart()
    {
        Qore::service(Supervisor::class)->stopProcess($this->name());
        Qore::service(Supervisor::class)->startProcess($this->name());
    }

    /**
     * start
     *
     */
    public function start()
    {
        Qore::service(Supervisor::class)->startService($this->name());
    }

    /**
     * start
     *
     */
    public function stop()
    {
        Qore::service(Supervisor::class)->stopService($this->name());
    }

    /**
     * subscribe
     *
     */
    public static function subscribe()
    {
        static::before('save', function($_event) {
            $entity = $_event->getTarget();
            $entity->autostart = (bool)$entity->autostart;
            $entity->autorestart = (bool)$entity->autorestart;
        });

        static::after('save', function($_event) {
            Qore::service(SupervisorConfigurator::class)->build($service = $_event->getTarget());
            Qore::service(Supervisor::class)->restart();
        });
    }

}
