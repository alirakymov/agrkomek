<?php

namespace Qore\SynapseManager\Plugin\Operation;

use Qore\SynapseManager\Structure\Entity\SynapseBaseEntity;
use Ramsey\Uuid\Uuid;

abstract class AbstractStorage extends SynapseBaseEntity implements StorageInterface
{
    /**
     * @inheritdoc
     */
    public function getIdentifier(): string
    {
        return $this['identifier'];
    }

    /**
     * @inheritdoc
     */
    public function setData(array $_data): StorageInterface
    {
        $this['data'] = $_data;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getData(): array
    {
        return $this['data'];
    }

    /**
     * subscribe
     *
     */
    public static function subscribe()
    {
        static::before('initialize', function($_event){
            $params = $_event->getParams();
            $entity = $params['entityData'];
            if (isset($entity['data'])) {
                $entity['data'] = is_string($entity['data'])
                    ? unserialize($entity['data'])
                    : $entity['data'];
            } else {
                $entity['data'] = [];
            }
        });

        static::after('initialize', function($_event){
            $entity = $_event->getTarget();
            if ($entity->isNew() && ! isset($entity['identifier'])) {
                $entity['identifier'] = Uuid::uuid4()->toString();
            }
        });

        static::before('save', function($_event){
            $entity = $_event->getTarget();
            if (isset($entity['data'])) {
                $entity['data'] = is_array($entity['data'])
                    ? serialize($entity['data'])
                    : $entity['data'];
            } else {
                $entity['data'] = json_encode([]);
            }
        });

        static::after('save', function($_event){
            $entity = $_event->getTarget();
            if (isset($entity['data'])) {
                $entity['data'] = is_string($entity['data'])
                    ? unserialize($entity['data'])
                    : $entity['data'];
            }
        });

        parent::subscribe();
    }
}
