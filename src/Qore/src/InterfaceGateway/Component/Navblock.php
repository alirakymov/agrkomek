<?php

namespace Qore\InterfaceGateway\Component;

class Navblock extends AbstractComponent
{
    /**
     * @var string
     */
    protected $type = 'qc-navblock';

    /**
     * Set items of navigation
     *
     * @param array $_items
     *     ...
     *     [
     *         'title' => 'Item title',
     *         'description' => 'Item Description',
     *         'actionUri' => '/some-item-route',
     *     ]
     *     ...
     *
     * @return Navblock
     */
    public function setItems(array $_items) : Navblock
    {
        $this->setOption('items', $_items);
        return $this;
    }

}
