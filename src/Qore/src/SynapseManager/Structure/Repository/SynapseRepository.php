<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Artificer;

use Qore\ORM;
use Qore\Collection\Collection;
use Qore\SynapseManager\Artificer;

class SynapseRepository extends AbstractRepository
{
    /**
     * get
     *
     * @param string $_name
     */
    public function get(string $_name) : ?ArtificerInterface
    {
        return $this->findByName($_name);
    }

    /**
     * findByName
     *
     * @param string $_name
     */
    public function findByName(string $_name) : ?Artificer\SynapseArtificer
    {
        return $this->storage->match(['name' => $_name])->first();
    }

    /**
     * findByID
     *
     * @param int $_id
     */
    public function findByID($_id)
    {
        return $this->storage->match(['id' => $_id])->first();
    }

    /**
     * loadSubjects
     *
     */
    public function loadSubjects() : void
    {
        $mm = $this->sm->getModelManager();
        $synapses = $mm('QSynapse:Synapses')
            ->all();

        $this->storage = new Collection([]);
        $container = $this->sm->getContainer();

        foreach ($synapses as $synapse) {
            $artificer = $container->build(Artificer\Synapse\SynapseArtificer::class, [
                'synapse' => $synapse,
            ]);
            $this->storage->append($artificer);
        }
    }

}
