<?php

namespace Qore\SynapseManager\Plugin\Designer;

use Qore\SynapseManager\Plugin\Designer\InterfaceGateway\DesignerComponent;
use Qore\SynapseManager\Structure\Entity\SynapseBaseEntity;

abstract class ComposableEntity extends SynapseBaseEntity implements ComposableEntityInterface
{
    /**
     * @inheritdoc
     */
    public function setUnique(string $_unique): ComposableEntityInterface
    {
        $this['__options'] = array_merge(
            $this['__options'] ?? [],
            [CanvasState::BLOCK_ID => $_unique]
        );

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getUnique(): ?string
    {
        return $this['__options'][CanvasState::BLOCK_ID] ?? null;
    }

    /**
     * Subscribe to entity events
     *
     * @return void
     */
    public static function subscribe()
    {
        static::after('initialize', function($_event) {
            $entity = $_event->getTarget();
            /* is_null($entity->getUnique()) && $entity->setUnique(Uuid::uuid4()); */
        });

        parent::subscribe();
    }

}
