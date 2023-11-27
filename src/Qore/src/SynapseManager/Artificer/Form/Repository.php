<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Artificer\Form;

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
     * findByName
     *
     * @param string $_name
     */
    public function findByName(string $_name) : ?Artificer\ArtificerInterface
    {
        return $this->storage->filter(function($_form) use ($_name) {
            return $_name == $_form->getNameIdentifier();
        })->first();
    }

    /**
     * findByID
     *
     * @param int $_id
     */
    public function findByID($_id) : ?Artificer\ArtificerInterface
    {
        return $this->storage->filter(function($_form) use ($_id) {
            return (int)$_id == (int)$_form->getIdentifier();
        })->first();
    }

    /**
     * findByClassName
     *
     * @param string $_name
     */
    public function findByClassName(string $_name) : ?Artificer\ArtificerInterface
    {
        return $this->storage->filter(function($_form) use ($_name) {
            return $_name == get_class($_form);
        })->first();
    }

}
