<?php

declare(strict_types=1);

namespace Qore\ORM\Gateway;

use Qore\ORM\ModelManager;
use Qore\ORM\Entity;

/**
 * Class: ProcessorRepository
 *
 * @see ProcessorRepositoryInterface
 */
class ProcessorRepository implements ProcessorRepositoryInterface
{
    /**
     * entityName
     *
     * @var mixed
     */
    protected $entityName = null;

    /**
     * _mm
     *
     * @var mixed
     */
    protected $mm = null;

    /**
     * entities
     *
     * @var mixed
     */
    protected $entities = [];

    /**
     * __construct
     *
     * @param string $_entity
     * @param ModelManager $_mm
     */
    public function __construct(string $_entityName, ModelManager $_mm)
    {
        $this->entityName = $_entityName;
        $this->mm = $_mm;
    }

    /**
     * set
     *
     * @param mixed $_entityData
     */
    public function set($_entityData) : Entity\EntityInterface
    {
        if (isset($this->entities[$_entityData['id']])) {
            return $this->entities[$_entityData['id']];
        }

        if (is_object($_entityData)) {
            if (! $_entityData instanceof Entity\EntityInterface) {
                throw new Exception\UnknownEntity(vsprintf('Entity object (%s) must be instance of %s', [get_class($_entityData), Entity\EntityInterface::class]));
            }
            $entity = $_entityData;
            $this->entities[$entity->id] = $entity;
            $this->mm->registerEntity($entity);
        } else {
            $entity = $this->mm->getEntity($this->entityName, $_entityData);
            $this->entities[$entity->id] = $entity;
        }

        return $entity;
    }

    /**
     * unset
     *
     * @param mixed $_id
     */
    public function unset($_id = null)
    {
        if (is_null($_id)) {
            $entities = isset($this->entities[$_id]) ? [$this->entities[$_id]] : [];
        } else {
            $entities = $this->entities;
        }

        foreach ($entities as $entity) {
            $this->mm->getEntityProvider()->unset($entity);
        }
    }

    /**
     * get
     *
     * @param mixed $_id
     */
    public function get($_id) : ?Entity\EntityInterface
    {
        return $this->entities[$_id] ?? null;
    }

    /**
     * getAll
     *
     */
    public function getAll() : array
    {
        return $this->entities;
    }

    /**
     * find
     *
     * @param mixed $_id
     */
    public function find($_id)
    {
        foreach ($this->storage as $key => $entity) {
            if ($entity->id === $_id) {
                return $entity;
            }
        }

        return null;
    }

    /**
     * exchange
     *
     * @param array $_entiites
     */
    public function exchange(array $_entiites) : void
    {
        $this->entities = [];

        foreach ($_entities as $entity) {
            $this->set($entity);
        }
    }

    /**
     * count
     *
     */
    public function count() : int
    {
        return count($this->entities);
    }

    /**
     * flush
     *
     */
    public function flush() : void
    {
        $this->entities = [];
    }

    /**
     * getEntityName
     *
     */
    public function getEntityName() : string
    {
        return $this->entityName;
    }

}
