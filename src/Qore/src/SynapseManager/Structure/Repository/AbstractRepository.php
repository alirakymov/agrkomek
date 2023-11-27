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
    private $storage = null;

    /**
     * sm
     *
     * @var mixed
     */
    private $sm = null;

    /**
     * __construct
     *
     * @param $_storage
     */
    public function __construct(Collection $_storage = [])
    {
        if ($_storage) {
            $this->storage = $storage;
        } else {
            $this->loadSubjects();
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
    }

    /**
     * getSynapseManager
     *
     */
    public function getSynapseManager() : SynapseManager
    {
        return $this->sm;
    }

}
