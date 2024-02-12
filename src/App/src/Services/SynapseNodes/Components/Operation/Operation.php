<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\Operation;

use Qore\Qore;
use Qore\SynapseManager\Structure\Entity\SynapseBaseEntity;

/**
 * Class: Operation
 *
 * @method CollectionInterface phases();
 *
 * @see Qore\SynapseManager\Structure\Entity\SynapseBaseEntity
 */
class Operation extends SynapseBaseEntity
{
    /** 
     * @var string - событие создания новой заявки
     */
    const OPERATION_DEMAND_NEW = 'operation.demand.new';

    /** 
     * @var string - событие создания новой заявки
     */
    const OPERATION_DEMAND_OPEN = 'operation.demand.open';

    /** 
     * @var string - событие на изменение статуса в заявке 
     */
    const OPERATION_DEMAND_SET_STATUS = 'operation.demand.status.set';

    /** 
     * @var string - событие на изменение исполнителя в заявке 
     */
    const OPERATION_DEMAND_SET_ASSIGNEE = 'operation.demand.assignee.set';

    /** 
     * @var string - событие на изменение исполнителя в заявке 
     */
    const OPERATION_DEMAND_SET_GROUP = 'operation.demand.group.set';

    /** 
     * @var string - Событие на назначение клиента 
     */
    const OPERATION_DEMAND_SET_PARTNER = 'operation.demand.partner.set';

    /** 
     * @var string - Событие на назначение клиента 
     */
    const OPERATION_DEMAND_SET_FOLLOWERS = 'operation.demand.followers.set';

    /** 
     * @var string - Событие на назначение клиента 
     */
    const OPERATION_DEMAND_UNSET_FOLLOWERS = 'operation.demand.followers.unset';

    /** 
     * @var string - событие на комментарий в заявке 
     */
    const OPERATION_DEMAND_SET_COMMENT = 'operation.demand.comment.set';

    /** 
     * @var string - событие на новый email в заявке 
     */
    const OPERATION_DEMAND_MESSAGE_REGISTER = 'operation.demand.message.register';

    /** 
     * @var string - событие на новый email в заявке 
     */
    const OPERATION_DEMAND_MESSAGE_SEND = 'operation.demand.message.send';

    /** 
     * @var string - событие на отправленное сообщение
     */
    const OPERATION_SEND_MAIL_SUCCESS = 'operation.mail.message.sent.success';

    /** 
     * @var string - событие на не отправленное сообщение
     */
    const OPERATION_SEND_MAIL_FAILURE = 'operation.mail.message.sent.failure';

    /** 
     * @var string - новое сообщение в бот телеграм
     */
    const OPERATION_TELEGRAM_NEW_MESSAGE = 'operation.telegram.message.new';

    /** 
     * @var string - регистрация нового этапа продукта в заявке
     */
    const OPERATION_DEMAND_SET_PRODUCT_STAGE = 'operation.demand.set.product.stage';

    /**
     * Get events list
     *
     * @return array
     */
    public static function getEvents(): array
    {
        return [
            [
                'id' => self::OPERATION_DEMAND_NEW,
                'label' => 'Создание новой заявки',
            ],
            [
                'id' => self::OPERATION_DEMAND_OPEN,
                'label' => 'Открытие заявки',
            ],
            [
                'id' => self::OPERATION_DEMAND_SET_ASSIGNEE,
                'label' => 'Изменение исполнителя заявки',
            ],
            [
                'id' => self::OPERATION_DEMAND_SET_GROUP,
                'label' => 'Назначение новой группы в заявке',
            ],
            [
                'id' => self::OPERATION_DEMAND_SET_PARTNER,
                'label' => 'Изменение клиента в заявке',
            ],
            [
                'id' => self::OPERATION_DEMAND_SET_FOLLOWERS,
                'label' => 'Добавление подписчиков в заявке',
            ],
            [
                'id' => self::OPERATION_DEMAND_UNSET_FOLLOWERS,
                'label' => 'Удаление подписчиков в заявке',
            ],
            [
                'id' => self::OPERATION_DEMAND_SET_STATUS,
                'label' => 'Изменение статуса заявки',
            ],
            [
                'id' => self::OPERATION_DEMAND_SET_COMMENT,
                'label' => 'Новый комментарий в заявке',
            ],
            [
                'id' => self::OPERATION_DEMAND_MESSAGE_REGISTER,
                'label' => 'Новое синхронизированное письмо',
            ],
            [
                'id' => self::OPERATION_DEMAND_MESSAGE_SEND,
                'label' => 'Новое исходящее письмо в заявке',
            ],
            [
                'id' => self::OPERATION_SEND_MAIL_SUCCESS,
                'label' => 'Событие на успешно отправленное письмо',
            ],
            [
                'id' => self::OPERATION_SEND_MAIL_FAILURE,
                'label' => 'Ошибка при отправке письма',
            ],
            [
                'id' => self::OPERATION_TELEGRAM_NEW_MESSAGE,
                'label' => 'Новое сообщение в telegram-бот',
            ],
        ];
    }

    /**
     * subscribe
     *
     */
    public static function subscribe()
    {
        parent::subscribe();
    }

}
