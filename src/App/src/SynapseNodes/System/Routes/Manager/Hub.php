<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\System\Routes\Manager;

use Qore\NotifyManager\HubAbstract;
use Qore\NotifyManager\HubInterface;

/**
 * Class: Hub
 *
 * @see HubInterface
 * @see HubAbstract
 */
class Hub extends HubAbstract implements HubInterface
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
        # - Пример для хаба в котором целевой объект хаба есть сам клиент
        return [static::generateToken($_client->getUniqueHash())];
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
