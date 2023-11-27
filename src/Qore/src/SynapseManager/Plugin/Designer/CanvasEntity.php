<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Plugin\Designer;

use Qore\SynapseManager\Structure\Entity\SynapseBaseEntity;

/**
 * Class: LessonContent
 *
 * @see Entity\SynapseBaseEntity
 */
abstract class CanvasEntity extends SynapseBaseEntity
{
    /**
     * Get canvas state attribute name
     *
     * @return string
     */
    abstract public function getCanvasAttributeName() : string;

    /**
     * Get canvas state instance
     *
     * @return CanvasState
     */
    public function getCanvasState() : CanvasState
    {
        $attributeName = $this->getCanvasAttributeName();

        if (! isset($this[$attributeName])) {
            $this[$attributeName] = new CanvasState();
        }

        return $this[$attributeName];
    }

    /**
     * Set canvas state instance
     *
     * @param CanvasState $_canvasState
     *
     * @return CanvasEntity
     */
    public function setCanvasState(CanvasState $_canvasState) : CanvasEntity
    {
        $this[$this->getCanvasAttributeName()] = $_canvasState;
        return $this;
    }

    /**
     * subscribe
     *
     */
    public static function subscribe()
    {
        static::after('initialize', function($_event) {
            $entity = $_event->getTarget();
            $attributeName = $entity->getCanvasAttributeName();
            if (isset($entity[$attributeName])) {
                $entity[$attributeName] = is_string($entity[$attributeName])
                    ? json_decode($entity[$attributeName], true)
                    : $entity[$attributeName];

                if (is_array($entity[$attributeName])) {
                    $entity[$attributeName] = new CanvasState($entity[$attributeName]);
                }
            }
        });

        static::before('save', function($_event) {
            $entity = $_event->getTarget();
            $attributeName = $entity->getCanvasAttributeName();

            if (isset($entity[$attributeName])) {
                $entity[$attributeName] = is_object($entity[$attributeName]) && $entity[$attributeName] instanceof CanvasState
                    ? $entity[$attributeName]->prepare()
                    : $entity[$attributeName];
                $entity[$attributeName] = is_array($entity[$attributeName])
                    ? json_encode($entity[$attributeName])
                    : $entity[$attributeName];
            }
        });

        static::after('save', function($_event) {
            $entity = $_event->getTarget();
            $attributeName = $entity->getCanvasAttributeName();

            if (isset($entity[$attributeName])) {
                $entity[$attributeName] = is_string($entity[$attributeName])
                    ? json_decode($entity[$attributeName], true)
                    : $entity[$attributeName];
                if (is_array($entity[$attributeName])) {
                    $entity[$attributeName] = new CanvasState($entity[$attributeName]);
                }
            }

        });

        parent::subscribe();
    }

}
