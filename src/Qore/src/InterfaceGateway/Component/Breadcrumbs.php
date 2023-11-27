<?php

namespace Qore\InterfaceGateway\Component;

class Breadcrumbs extends AbstractComponent
{
    /**
     * type
     *
     * @var string
     */
    protected $type = 'qc-breadcrumbs';

    /**
     * Set breadcrumbs items
     *
     * @param array $_items
     *
     * @return Breadcrumbs
     */
    public function setItems(array $_items) : Breadcrumbs
    {
        $this->setOption('items', array_values($_items));
        return $this;
    }

}
