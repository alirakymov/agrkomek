<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\Consultancy;

use Qore\Qore;
use Qore\SynapseManager\Structure\Entity\SynapseBaseEntity;

/**
 * Class: Article
 *
 * @see Qore\SynapseManager\Structure\Entity\SynapseBaseEntity
 */
class Consultancy extends SynapseBaseEntity
{
    /**
     * Is Answered
     *
     * @return Consultancy
     */
    public function isAnswered(): Consultancy
    {
        $this->isUpdated = 0;
        return $this;
    }

    /**
     * Is updated 
     *
     * @return Consultancy
     */
    public function isUpdated(): Consultancy
    {
        $this->isUpdated = 1;
        return $this;
    }

    /**
     * subscribe
     *
     */
    public static function subscribe()
    {
        static::before('save', function($_event) {
            $entity = $_event->getTarget();
            $entity->content = is_string($entity->content) 
                ? $entity->content 
                : json_encode($entity->content, JSON_UNESCAPED_UNICODE);
        });

        static::after('save', $func = function($_event) {
            $entity = $_event->getTarget();
            $entity->content = is_string($entity->content) 
                ? json_decode($entity->content, true) 
                : $entity->content;
        });

        static::after('initialize', $func);

        parent::subscribe();
    }

}
