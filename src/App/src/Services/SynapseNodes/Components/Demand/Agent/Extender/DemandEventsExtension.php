<?php

namespace Qore\App\SynapseNodes\Components\Demand\Agent\Extender;

use Qore\App\SynapseNodes\Components\Demand\Demand;
use Qore\App\SynapseNodes\Components\Demand\DemandExtensionInterface;
use Qore\App\SynapseNodes\Components\DemandEvent\DemandEventDecorator;
use Qore\Collection\CollectionInterface;
use Qore\ORM\ModelManager;
use Qore\Qore;

class DemandEventsExtension implements DemandExtensionInterface
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
                    $_select->limit($_options['limit'])->order('@this.events.__created desc');
                })->with('events', fn($_gw) => $_gw->with('initiator'))
                ->where(['@this.id' => $_demand['id']])
                ->all();
            # - Запускаем декоратор для наших событий
            (new DemandEventDecorator($_demand->events(), $_demand))->decorate();
        })->compile());
    }
    
}
