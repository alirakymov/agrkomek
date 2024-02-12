<?php

namespace Qore\App\SynapseNodes\Components\Demand\Agent\InterfaceGateway;

use Closure;
use Qore\App\SynapseNodes\Components\Demand\Demand;
use Qore\Collection\CollectionInterface;
use Qore\InterfaceGateway\Component\AbstractComponent;

/**
 * Demand component - InterfaceGateway class for decorate demand component data
 *
 * @see AbstractComponent 
 */
class DemandListComponent extends AbstractComponent
{
    protected $type = 'qc-demand-list';

    /**
     * Set list of demands
     *
     * @param \Qore\Collection\CollectionInterface|null $_data (optional)
     *
     * @return DemandListComponent 
     */
    public function setDemands(?CollectionInterface $_data = null): DemandListComponent
    {
        if (! is_null($_data)) {
            $_data = $_data->map(function(Demand $_demand) {
                # - Decorate demand title
                $_demand['formatted-unique'] = $_demand->formatUnique();
                # - Decorate demand title
                $_demand['title'] = implode(' ', preg_split('/[\r\n\s\t]+/', strip_tags(str_replace('<', ' <', $_demand['title']))));
                # - Decorate agent
                isset($_demand['assignee']) && ! is_null($_demand->assignee()) 
                    && $_demand['assignee'] = $_demand->assignee()->decorate();
                # - Convert to array
                return $_demand->toArray(true); 
            })->toList();
        }

        return $this->setOption('demands', $_data);
    }

}
