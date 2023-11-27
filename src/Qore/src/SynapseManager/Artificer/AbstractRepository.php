<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Artificer;

use Qore\Collection\Collection;
use Qore\SynapseManager\SynapseManager;

abstract class AbstractRepository implements RepositoryInterface
{
    /**
     * storage
     *
     * @var mixed
     */
    protected $storage = null;

    /**
     * sm
     *
     * @var mixed
     */
    protected $sm = null;

    /**
     * __construct
     *
     * @param Collection $_storage
     */
    public function __construct(Collection $_storage = null)
    {
        $this->storage = $_storage;
    }

    /**
     * init
     *
     * @param SynapseManager $_sm
     */
    public function init(SynapseManager $_sm)
    {
        $this->sm = $_sm;

        foreach ($this->storage as $artificer) {
            $artificer->setSynapseManager($_sm);
            $artificer->init();
        }
    }

    /**
     * setSynapseManager
     *
     * @param SynapseManager $_sm
     */
    public function setSynapseManager(SynapseManager $_sm)
    {
        $this->sm = $_sm;

        foreach ($this->storage as $artificer) {
            $artificer->setSynapseManager($_sm);
        }
    }

    /**
     * getSynapseManager
     *
     */
    public function getSynapseManager() : SynapseManager
    {
        return $this->sm;
    }

    /**
     * getAll
     *
     */
    public function getAll() : Collection
    {
        return $this->storage;
    }

    /**
     * splitName
     *
     * @param string $_name
     */
    protected function splitName(string $_name) : array
    {
        return explode(':', $_name);
    }

}
