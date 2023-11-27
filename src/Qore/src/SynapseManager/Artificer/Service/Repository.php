<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Artificer\Service;

use Cake\Collection\Iterator\FilterIterator;
use Closure;
use Qore\Collection\CollectionInterface;
use Qore\ORM;
use Qore\Collection\Collection;
use Qore\SynapseManager\Artificer;

class Repository extends Artificer\AbstractRepository
{
    /**
     * get
     *
     * @param string $_name
     */
    public function get(string $_name) : ?Artificer\ArtificerInterface
    {
        return $this->findByName($_name);
    }

    /**
     * filter service repository
     *
     * @param Closure $_closure
     *
     * @return \Cake\Collection\Iterator\FilterIterator
     */
    public function filter(Closure $_closure) : FilterIterator
    {
        return $this->storage->filter($_closure);
    }

    /**
     * findByName
     *
     * @param string $_name
     */
    public function findByName(string $_name) : ?Artificer\ArtificerInterface
    {
        list($synapseName, $serviceName) = $this->splitName($_name);

        return $this->storage->filter(function($_service) use ($_name) {
            return $_name == $_service->getNameIdentifier();
        })->first();
    }

    /**
     * findByID
     *
     * @param int $_id
     */
    public function findByID($_id) : ?Artificer\ArtificerInterface
    {
        return $this->storage->filter(function($_service) use ($_id) {
            return (int)$_id == (int)$_service->getIdentifier();
        })->first();
    }

    /**
     * findByClassName
     *
     * @param string $_name
     */
    public function findByClassName(string $_name) : ?Artificer\ArtificerInterface
    {
        return $this->storage->filter(function($_service) use ($_name) {
            return $_name == get_class($_service);
        })->first();
    }

}
