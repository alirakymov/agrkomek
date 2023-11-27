<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\ModeratorPermission;

use Qore\Qore;
use Qore\SynapseManager\Structure\Entity\SynapseBaseEntity;

/**
 * Class: ModeratorPermission
 *
 * @see Qore\SynapseManager\Structure\Entity\SynapseBaseEntity
 */
class ModeratorPermission extends SynapseBaseEntity
{
    /**
     * Get components list
     *
     * @return array
     */
    public static function getComponents(): array
    {
        return [
            ['id' => 'article', 'label' => 'Новостная лента'],
            ['id' => 'guide', 'label' => 'Справочник'],
            ['id' => 'moderator', 'label' => 'Модераторы'],
        ];
    }

    /**
     * Get levels
     *
     * @return array
     */
    public static function getLevels(): array
    {
        return [
            ['id' => 1, 'label' => 'Просмотр'],
            ['id' => 100, 'label' => 'Редактирование'],
            ['id' => 200, 'label' => 'Удаление'],
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
