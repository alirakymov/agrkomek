<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\Notification;

use Qore\Qore;
use Qore\SynapseManager\Structure\Entity\SynapseBaseEntity;

/**
 * Class: NotificationMessage
 *
 * @see Qore\SynapseManager\Structure\Entity\SynapseBaseEntity
 */
class Notification extends SynapseBaseEntity
{
    const EVENT_MACHINERY_STATUS_UPDATE = 'status_update';
    const EVENT_CHAT_UPDATE = 'chat_update';

    /**
     * Array
     *
     * @return array
     */
    public static function getEvents(): array
    {
        return [
            static::EVENT_MACHINERY_STATUS_UPDATE => 'Обновление статуса объявлений',
            static::EVENT_CHAT_UPDATE => 'Обновление в чатах',
        ];
    }

    /**
     * subscribe
     *
     */
    public static function subscribe()
    {
        static::before('save', function($_event) {
            $entity = $_event->getTarget();
            $entity->data = is_string($entity->data) 
                ? $entity->data 
                : json_encode($entity->data, JSON_UNESCAPED_UNICODE);
        });

        static::after('save', $func = function($_event) {
            $entity = $_event->getTarget();
            $entity->data = is_string($entity->data) 
                ? json_decode($entity->data, true) 
                : $entity->data;
        });

        static::after('initialize', $func);

        parent::subscribe();
    }

}
