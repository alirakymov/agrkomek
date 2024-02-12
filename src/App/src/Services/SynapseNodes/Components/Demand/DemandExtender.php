<?php

namespace Qore\App\SynapseNodes\Components\Demand;

use Qore\Collection\CollectionInterface;
use Qore\Qore;

class DemandExtender implements DemandExtenderInterface
{
    /**
     * @var array - extenders list 
     */
    private array $extenders;

    /**
     * @inheritdoc
     */
    public function with(array $_extenders): DemandExtenderInterface
    {
        $this->extenders = $_extenders;     
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function populate($_data): CollectionInterface
    {
        if (! $_data instanceof CollectionInterface) {
            $_data = Qore::collection(is_array($_data) ? $_data : [$_data]);
        }

        if (! $_data->count()) {
            return $_data;
        }

        foreach ($this->extenders as $extender => $options) {
            if (is_int($extender)) {
                $extender = $options;
                $options = [];
            }

            (new $extender())->populate($_data, $options);
        }
        
        return $_data;
    }

}
