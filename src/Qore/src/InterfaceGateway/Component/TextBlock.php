<?php

namespace Qore\InterfaceGateway\Component;

class TextBlock extends AbstractComponent
{
    /**
     * type
     *
     * @var string
     */
    protected $type = 'qc-text';

    /**
     * Set breadcrumbs items
     *
     * @param string $_text
     *
     * @return Breadcrumbs
     */
    public function setText(string $_text) : TextBlock
    {
        $this->setOption('text', $_text);
        return $this;
    }

}
