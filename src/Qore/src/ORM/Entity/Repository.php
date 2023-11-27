<?php

declare(strict_types=1);

namespace Qore\ORM\Entity;

use Qore\ORM\ModelManager;
use Qore\ORM\Entity;

/**
 * Class: Repository
 *
 * @see RepositoryInterface
 */
class Repository implements RepositoryInterface
{
    /**
     * entityClass
     *
     * @var mixed
     */
    protected $entityClass = null;

    /**
     * storage
     *
     * @var mixed
     */
    protected $storage = [];

    /**
     * __construct
     *
     * @param string $_entity
     */
    public function __construct(string $_entityClass)
    {
        $this->entityClass = $_entityClass;
    }

    /**
     * set
     *
     * @param EntityInterface $_entityData
     */
    public function set(EntityInterface $_entity) : EntityInterface
    {
        if ($_entity->isKept() && is_null($this->find($_entity))) {
            $this->storage[$_entity->id] = $_entity;
        }

        return $_entity;
    }

    /**
     * set
     *
     * @param EntityInterface $_entityData
     */
    public function register(EntityInterface $_entity) : EntityInterface
    {
        $entity = $this->find($_entity);
        if ($_entity->isKept() && is_null($entity)) {
            $this->storage[$_entity->id] = $_entity;
        } elseif (! is_null($entity)) {
            # - Now I trust in God
            $this->storage[$entity->id] = $_entity->combine($entity->combine($_entity));
        }

        return $_entity;
    }

    /**
     * unset
     *
     * @param mixed $_id
     */
    public function unset($_id) : bool
    {
        foreach ($this->storage as $key => $entity) {
            if ($entity->id === $_id) {
                unset($this->storage[$key]);
                return true;
            }
        }
        return false;
    }

    /**
     * find
     *
     * @param mixed $_id
     */
    public function find($_entityOrID) : ?EntityInterface
    {
        if (is_scalar($_entityOrID)) {
            $entityID = $entityInsertID = $_entityOrID;
        } elseif (is_array($_entityOrID) || is_object($_entityOrID) && $_entityOrID instanceof EntityInterface) {
            $entityID = $_entityOrID['id'] ?? null;
            $entityInsertID = $_entityOrID['__idinsert'] ?? null;
        } else {
            throw new Exception\EntityException(sprintf('Undefined type of object (%s)', is_object($_entityOrID) ? get_class($_entityOrID) : gettype($_entityOrID)));
        }

        return $this->storage[$entityID] ?? null;

        foreach ($this->storage as $key => $entity) {
            if ((string)$entity->id === (string)$entityID || (! is_null($entityInsertID) && $entity->__idinsert === $entityInsertID)) {
                return $entity;
            }
        }

        return null;
    }

    /**
     * getAll
     *
     */
    public function getAll() : array
    {
        return $this->storage;
    }

    /**
     * exchange
     *
     * @param array $_entities
     */
    public function exchange(array $_entities) : void
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
    public function getEntityClass() : string
    {
        return $this->entityClass;
    }

}
