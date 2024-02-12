<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\System\PageWidget;

use Qore\Qore;
use Qore\SynapseManager\Structure\Entity;

/**
 * Class: PageWidget
 *
 * @see Entity\SynapseBaseEntity
 */
class PageWidget extends Entity\SynapseBaseEntity
{
    private static $systemServices = [
        'group-service' => 'Группирующий сервис',
    ];

    /**
     * Return system service label
     *
     * @return ?string
     */
    public function getSystemServiceLabel() : ?string
    {
        return $this->isSystemService() ? static::$systemServices[$this->service] : null;
    }

    /**
     * Check service type for non exsits system services
     *
     * @return bool
     */
    public function isSystemService() : bool
    {
        return in_array($this->service, array_keys(static::$systemServices));
    }

    /**
     * subscribe
     *
     */
    public static function subscribe()
    {
        parent::subscribe();
    }

    /**
     * return all defined system services
     *
     * @return
     */
    public static function getSystemServices() : array
    {
        return static::$systemServices;
    }

}
