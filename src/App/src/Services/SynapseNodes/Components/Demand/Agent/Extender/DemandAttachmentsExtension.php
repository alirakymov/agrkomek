<?php

namespace Qore\App\SynapseNodes\Components\Demand\Agent\Extender;

use Qore\App\SynapseNodes\Components\Demand\Demand;
use Qore\App\SynapseNodes\Components\Demand\DemandExtensionInterface;
use Qore\App\SynapseNodes\Components\DemandEvent\DemandEventDecorator;
use Qore\Collection\CollectionInterface;
use Qore\ORM\ModelManager;
use Qore\Qore;

class DemandAttachmentsExtension implements DemandExtensionInterface
{
    /**
     * @inheritdoc
     */
    public function populate(CollectionInterface $_data, array $_options): CollectionInterface
    {
        /** @var ModelManager */
        $mm = Qore::service(ModelManager::class);

        return Qore::collection($_data->each(function(Demand $_demand) use ($mm, $_options) {
            $mm('SM:Demand')
                ->select(function($_select) use ($_options) {
                    isset($_options['limit']) && $_select->limit($_options['limit']);
                    $_select->order('@this.attachments.__created desc');
                })->with('attachments')
                ->where(['@this.id' => $_demand['id']])
                ->all();
        })->compile());
    }
    
}
