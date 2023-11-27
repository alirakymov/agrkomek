<?php

namespace Qore\SynapseManager\Plugin\Indexer;

use Qore\ORM\Entity\Entity;

class SynapsePluginIndexer extends Entity
{
    /**
     * Reset index date
     *
     * @return SynapsePluginIndexer
     */
    public function resetIndexDate() : SynapsePluginIndexer
    {
        $this->lastIndexDate = new \DateTime('0000-00-00');
        return $this;
    }

    /**
     * Actualize index date
     *
     * @param string $_date (optional)
     *
     * @return SynapsePluginIndexer
     */
    public function updateIndexDate(string $_date = null) : SynapsePluginIndexer
    {
        $this->lastIndexDate = new \DateTime($_date ?? 'now');
        return $this;
    }

    /**
     * subscribe to events
     *
     * @return void
     */
    public static function subscribe()
    {
        parent::subscribe();

        static::after('initialize', function($e) {
            $entity = $e->getTarget();
            if ($entity->isNew() || isset($entity['lastIndexDate'])) {
                $entity->lastIndexDate = new \DateTime($entity['lastIndexDate'] ?? '0000-00-00');
            }

            if (isset($entity['mappingState'])) {
                $entity['mappingState'] = is_string($entity['mappingState'])
                    ? json_decode($entity['mappingState'], true)
                    : $entity['mappingState'];
            } else {
                $entity['mappingState'] = [];
            }
        });

        static::before('save', function($e) {
            $entity = $e->getTarget();
            if (! isset($entity['lastIndexDate']) || ! $entity['lastIndexDate']) {
                $entity->lastIndexDate = new \DateTime('0000-00-00');
            }

            if (isset($entity['lastIndexDate']) && is_object($entity->lastIndexDate) && $entity->lastIndexDate instanceof \DateTime) {
                $entity->lastIndexDate = $entity->lastIndexDate->format('Y-m-d H:i:s');
            }

            if (isset($entity['mappingState'])) {
                $entity['mappingState'] = ! is_string($entity['mappingState'])
                    ? json_encode($entity['mappingState'])
                    : $entity['mappingState'];
            } else {
                $entity['mappingState'] = json_encode([]);
            }
        });

        static::after('save', function($e) {
            $entity = $e->getTarget();
            if (isset($entity['lastIndexDate']) && is_string($entity->lastIndexDate)) {
                $entity->lastIndexDate = new \DateTime($entity['lastIndexDate'] ?: '0000-00-00');
            }

            if (isset($entity['mappingState'])) {
                $entity['mappingState'] = is_string($entity['mappingState'])
                    ? json_decode($entity['mappingState'], true)
                    : $entity['mappingState'];
            }
        });
    }
}
