<?php

namespace Qore\SynapseManager\Plugin\Report;

use Qore\ORM\Entity\Entity;
use Qore\Qore;
use Qore\UploadManager\UploadedFile;
use Qore\UploadManager\UploadManager;

class SynapsePluginReport extends Entity
{
    public function toArray(bool $_recursive = false, array $_predictRecursionChain = []): array
    {
        $result = parent::toArray($_recursive, $_predictRecursionChain);

        if (isset($result['filters'])) {
            unset($result['filters']);
        }

        return $result;
    }

    /**
     * Get file abstract layer object
     *
     * @return UploadManagerUploadedFile|null
     */
    public function file(): ?UploadedFile
    {
        return isset($this['fileUnique']) 
            ? Qore::service(UploadManager::class)->getFile($this['fileUnique']) 
            : null;
    }

    /**
     * subscribe to events
     *
     * @return void
     */
    public static function subscribe()
    {
        parent::subscribe();

        static::after('initialize', $init = function($e) {
            $entity = $e->getTarget();
            if ($entity->isNew() || isset($entity['completed'])) {
                $entity['completed'] = new \DateTime($entity['completed'] ?? '0000-00-00');
            }

            if (isset($entity['filters'])) {
                $entity['filters'] = is_string($entity['filters'])
                    ? unserialize($entity['filters'])
                    : $entity['filters'];
            } else {
                $entity['filters'] = [];
            }
        });

        static::before('save', function($e) {
            $entity = $e->getTarget();
            if (! isset($entity['completed']) || ! $entity['completed']) {
                $entity->completed = new \DateTime('0000-00-00');
            }

            if (isset($entity['completed']) && is_object($entity['completed']) && $entity['completed'] instanceof \DateTime) {
                $entity['completed'] = $entity['completed']->format('Y-m-d H:i:s');
            }

            if (isset($entity['filters'])) {
                $entity['filters'] = ! is_string($entity['filters'])
                    ? serialize($entity['filters'])
                    : $entity['filters'];
            } else {
                $entity['filters'] = serialize([]);
            }
        });

        static::after('save', $init);
    }

}
