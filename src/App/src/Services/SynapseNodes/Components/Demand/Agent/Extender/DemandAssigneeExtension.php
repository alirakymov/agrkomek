<?php

namespace Qore\App\SynapseNodes\Components\Demand\Agent\Extender;

use Qore\App\SynapseNodes\Components\Demand\Demand;
use Qore\App\SynapseNodes\Components\Demand\DemandExtensionInterface;
use Qore\Collection\CollectionInterface;
use Qore\ORM\ModelManager;
use Qore\Qore;

class DemandAssigneeExtension implements DemandExtensionInterface
{
    /**
     * @inheritdoc
     */
    public function populate(CollectionInterface $_data, array $_options): CollectionInterface
    {
        $mm = Qore::service(ModelManager::class);

        return $mm('SM:Demand')->with('assignee')->where(['@this.id' => $_data->extract('id')->toList()])->all();
    }
    
}
