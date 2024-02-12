<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\Partner;

use Qore\ImageManager\ImageManager;
use Qore\Qore;
use Qore\SynapseManager\Structure\Entity\SynapseBaseEntity;
use Qore\UploadManager\UploadManager;

/**
 * Class: Partner
 *
 * @see Qore\SynapseManager\Structure\Entity\SynapseBaseEntity
 */
class Partner extends SynapseBaseEntity
{
    /**
     * subscribe
     *
     */
    public static function subscribe()
    {
        static::after('initialize', function($e){
            $entity = $e->getTarget();
            if (isset($entity['file'])) {
                $file = $entity['file'];
                if (exif_imagetype($file->getStream()->getMetadata('uri')) === false) {
                    $entity['isValid'] = false;
                    return false;
                }

                $entity->name = $file->getClientFilename();
                $entity->size = $file->getStream()->getSize();
                $entity->isDirectory = 0;
            }

            if ( ! isset($entity['logotype']) && isset($entity['uniqid']) && $entity['uniqid']) {
                $entity['logotype'] = Qore::service(ImageManager::class)->init(
                    Qore::service(UploadManager::class)->getFile($entity['uniqid'])
                );
                $entity['logotype-url'] = $entity['logotype']->getUri();
            }
        });

        static::before('save', function($e){
            $entity = $e->getTarget();

            if (isset($entity->isValid) && $entity->isValid === false) {
                return false;
            }

            if (! isset($entity->uniqid) && isset($entity['file'])) {
                $entity->uniqid = Qore::service(UploadManager::class)->saveFile($entity['file']);
            }

            if (! isset($entity['__options']['sizes']) && isset($entity['uniqid'])) {
                $imageManager = Qore::service(ImageManager::class)->init(
                    Qore::service(UploadManager::class)->getFile($entity['uniqid'])
                );

                $optionsIsString = is_string($entity['__options']);

                $entity['__options'] = array_merge($optionsIsString ? json_decode($entity['__options'], true) ?? [] : ($entity['__options'] ?? []), ['sizes' => $imageManager->getOriginalSize()]);
                $optionsIsString && ($entity['__options'] = json_encode($entity['__options']));
            }
        });

        static::before('delete', function($e){
            $entity = $e->getTarget();
            $params = $e->getParams();

            if (! isset($entity['uniqid'])) {
                $entity = $params['mm']($entity->getEntityName())->where(function($_where) use ($entity){
                    $_where(['@this.id' => $entity->id]);
                })->one();
            }

            if (isset($entity['uniqid'])) {
                Qore::service(ImageManager::class)
                    ->init(Qore::service(UploadManager::class)->getFile($entity->uniqid))
                    ->remove();
            }
        });

        parent::subscribe();
    }

}
