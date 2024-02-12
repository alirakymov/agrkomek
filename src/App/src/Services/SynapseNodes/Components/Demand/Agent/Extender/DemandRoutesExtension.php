<?php

namespace Qore\App\SynapseNodes\Components\Demand\Agent\Extender;

use Qore\App\SynapseNodes\Components\Demand\DemandExtensionInterface;
use Qore\Collection\CollectionInterface;
use Qore\ORM\ModelManager;
use Qore\Qore;
use Qore\SynapseManager\SynapseManager;

class DemandRoutesExtension implements DemandExtensionInterface
{
    /**
     * @inheritdoc
     */
    public function populate(CollectionInterface $_data, array $_options): CollectionInterface
    {
        $sm = Qore::service(SynapseManager::class);

        return Qore::collection($_data->map(function ($_demand) use ($sm) {
            $service = $sm('Demand:Agent');
            $_demand['routes'] = [
                'save' => Qore::url($service->getRouteName('save'), [ 'id' => $_demand['id'], ]),
                'view' => Qore::url($service->getRouteName('view'), [ 'id' => $_demand['id'], ]),
                'upload' => Qore::url($service->getRouteName('upload'), [ 'id' => $_demand['id'], ]),
                'set-status' => Qore::url($service->getRouteName('set-status'), [ 'id' => $_demand['id'], ]),
                'comment' => Qore::url($service->getRouteName('comment'), [ 'id' => $_demand['id'], ]),
            ];
            return $_demand;
        })->compile());
    }
}
