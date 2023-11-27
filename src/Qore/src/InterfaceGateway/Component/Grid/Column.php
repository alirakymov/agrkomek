<?php

namespace Qore\InterfaceGateway\Component\Grid;

use Qore\InterfaceGateway\Component\AbstractComponent;

class Column extends AbstractComponent
{
    /**
     * @var string
     */
    protected $type = 'qcg-column';

    /**
     * Set class option
     *
     * @param string $_class
     *
     * @return Column
     */
    public function setClass(string $_class) : Column
    {
        $this->setOption('style-class', $_class);
        return $this;
    }

    /**
     * Add class to class option
     *
     * @param string $_class
     *
     * @return Column
     */
    public function addClass(string $_class) : Column
    {
        $this->setOption('style-class', sprintf('%s %s', $this->getOption('style-class', ''), $_class));
        return $this;
    }

}
