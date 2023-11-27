<?php

declare(strict_types=1);

namespace Qore\ORM\Entity;

use Qore\ORM;

class Provider implements ProviderInterface
{
    /**
     * mm
     *
     * @var mixed
     */
    protected $mm = null;

    /**
     * repositories
     *
     * @var mixed
     */
    private $repositories = [];

    /**
     * __construct
     *
     */
    public function __construct()
    {
    }

    /**
     * initialize
     *
     */
    public function initialize(ORM\ModelManager $_mm) : void
    {
        $this->mm = $_mm;
    }

    /**
     * get
     *
     */
    public function get(string $_entityName, array $_entityData = []) : EntityInterface
    {
        $mapper = $this->mm->getMapper($_entityName);
        # - Normalize entity name (if is set without namespace or it's entity class name)
        $_entityName = $mapper->getEntityName($_entityName);
        # - Get repository
        $repository = $this->getRepository($_entityName);

        $entity = $repository->find($_entityData);
        # - Check if entity is absent in repository
        if ( isset($_entityData['__keep']) && $_entityData['__keep'] === false || is_null($entity)) {
            $entity = $this->initializeEntityObject($_entityName, $_entityData);
        } elseif (! is_null($entity)) {
            if (isset($_entityData['__fromSource']) && $_entityData['__fromSource']) {
                $entity->combine($this->initializeEntityObject($_entityName, $_entityData));
            } else {
                $entity->extend($_entityData);
            }
        }

        return $entity;
    }

    /**
     * Initialize and register entity
     *
     * @param string $_entityName
     * @param array $_entityData
     *
     * @return EntityInterface
     */
    private function initializeEntityObject($_entityName, $_entityData)
    {
        $mapper = $this->mm->getMapper($_entityName);
        # - Get entity class for create instance
        $entityClass = $mapper->getEntityClass($_entityName);
        # - Create entity
        $entity = new $entityClass($mapper->getTable($_entityName), $_entityData);
        # - Emit event initialize before
        # - Инициализация объекта в системе провайдера
        $responses = $this->mm->getEventManager()->trigger(
            $entityClass::getEventName('initialize', 'before'),
            null,
            [
                'entityName' => $_entityName,
                'entityData' => $entity,
                'mm' => $this->mm,
            ]
        );
        # - Set to repository new Entity
        $entity->isKept() && $this->getRepository($_entityName)->set($entity);
        # - Emit event initialize after
        $this->mm->getEventManager()->trigger(
            $entityClass::getEventName('initialize', 'after'),
            $entity,
            [
                'previousResponses' => $responses,
                'mm' => $this->mm,
            ]
        );
        return $entity;
    }

    /**
     * unset
     *
     */
    public function unset(EntityInterface $_entity)
    {
        $this->getRepository(get_class($_entity))->unset($_entity->id);
    }

    /**
     * register
     *
     * @param EntityInterface $_entity
     */
    public function register(EntityInterface $_entity)
    {
        return $this->getRepository($_entity->getEntityName())->register($_entity);
    }

    /**
     * getRepository
     *
     */
    public function getRepository(string $_entityClass) : Repository
    {
        if (! isset($this->repositories[$_entityClass])) {
            $this->repositories[$_entityClass] = new Repository($_entityClass);
        }

        return $this->repositories[$_entityClass];
    }

    /**
     * reset
     *
     */
    public function reset()
    {
        $this->repositories = [];
    }

}
