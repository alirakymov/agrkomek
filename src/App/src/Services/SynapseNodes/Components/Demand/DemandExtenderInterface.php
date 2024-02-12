<?php

namespace Qore\App\SynapseNodes\Components\Demand;

use Qore\Collection\CollectionInterface;

interface DemandExtenderInterface
{
    /**
     * Set list of extenders
     *
     * @param array $_extenders 
     *
     * @return DemandExtenderInterface 
     */
    public function with(array $_extenders): DemandExtenderInterface;

    /**
     * Populate data with list of extenders
     *
     * @param \Qore\Collection\CollectionInterface|Demand $_data 
     *
     * @return \Qore\Collection\CollectionInterface 
     */
    public function populate($_data): CollectionInterface;
}
