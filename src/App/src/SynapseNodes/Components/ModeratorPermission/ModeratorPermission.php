<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\ModeratorPermission;

use Qore\App\SynapseNodes\Components\Article\Manager\ArticleService;
use Qore\App\SynapseNodes\Components\Consultancy\Manager\ConsultancyService;
use Qore\App\SynapseNodes\Components\Guide\Manager\GuideService;
use Qore\App\SynapseNodes\Components\Moderator\Manager\ModeratorService;
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
            ['id' => ArticleService::class, 'label' => 'Новостная лента'],
            ['id' => GuideService::class, 'label' => 'Справочник'],
            ['id' => ModeratorService::class, 'label' => 'Модераторы'],
            ['id' => ConsultancyService::class, 'label' => 'Консультации'],
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
        static::before('save', function($_event) {
            $entity = $_event->getTarget();
            $entity->extra = is_string($entity->extra) 
                ? $entity->extra 
                : json_encode($entity->extra, JSON_UNESCAPED_UNICODE);
        });

        static::after('save', $func = function($_event) {
            $entity = $_event->getTarget();
            $entity->extra = is_string($entity->extra) 
                ? json_decode($entity->extra, true) 
                : $entity->extra;
        });

        static::after('initialize', $func);

        parent::subscribe();
    }

}
