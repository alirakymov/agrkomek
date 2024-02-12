<?php

namespace Qore\App\SynapseNodes\Components\Demand;

use Qore\Collection\CollectionInterface;

interface DemandExtensionInterface
{
    /**
     * Populate dota collection with extension data
     *
     * @param \Qore\Collection\CollectionInterface $_data 
     * @param array $_options 
     *
     * @return \Qore\Collection\CollectionInterface 
     */
    public function populate(CollectionInterface $_data, array $_options): CollectionInterface;

}
