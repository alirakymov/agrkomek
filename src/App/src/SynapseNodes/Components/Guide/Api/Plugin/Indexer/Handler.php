<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\Guide\Api\Plugin\Indexer;

use Qore\SynapseManager\Plugin\Indexer\Handler as PluginHandler;
use Qore\SynapseManager\Plugin\Indexer\Model;
use Qore\SynapseManager\Plugin\Indexer\ModelInterface;
use Qore\SynapseManager\Structure\Entity\SynapseServiceSubject;

class Handler extends PluginHandler
{
    /**
     * Map service data structure to index data structure
     *
     * @param ModelInterface $_model
     * @param SynapseServiceSubject|null $_subject
     *
     * @return bool
     */
    public function map(ModelInterface $_model) : bool
    {
        parent::map($_model);

        $state = $_model(Model::MAPPING_CURSOR);
        $properties = $state('properties');

        $properties['content'] = ['type' => 'json'];

        return true;
    }

}
