<?php

namespace Qore\Desk\NotifyHubs;

use Qore\NotifyManager\HubAbstract;
use Qore\NotifyManager\HubInterface;

/**
 * Class: SystemNotifyHub
 *
 * @see HubInterface
 * @see HubAbstract
 */
class SystemNotifyHub extends HubAbstract implements HubInterface
{
    public function getToken(): string
    {
        return self::generateToken($this->target);
    }

    public static function subscribe($_user) : array
    {
        return [self::generateToken($_user)];
    }

    private static function generateToken($_target)
    {
        return sha1(__CLASS__);
    }
}
