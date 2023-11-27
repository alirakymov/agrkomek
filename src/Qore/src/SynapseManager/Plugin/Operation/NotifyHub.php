<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Plugin\Operation;

use Qore\NotifyManager\HubAbstract;
use Qore\NotifyManager\HubInterface;

/**
 * Class: Hub
 *
 * @see HubInterface
 * @see HubAbstract
 */
class NotifyHub extends HubAbstract implements HubInterface
{

    /**
     * getToken - возвращаем токен для конкретной цели хаба
     *
     */
    public function getToken(): string
    {
        return static::generateToken($this->target);
    }

    /**
     * subscribe - определяем список токенов конкретного клиента (исходя из отношений клиента с целями данного хаба)
     *
     * @param $_client
     */
    public static function subscribe($_client): array
    {
        return [];
    }

    /**
     * generateToken
     *
     */
    public static function generateToken($_target): string
    {
        return sha1(__CLASS__ . serialize($_target));
    }

}
