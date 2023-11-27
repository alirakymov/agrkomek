<?php

declare(strict_types=1);

namespace Qore\InterfaceGateway\Component;

class Modal extends AbstractComponent
{
    /**
     * @var string
     */
    const RIGHTSIDE = 'rightside';

    /**
     * @var string
     */
    const SIZE_NM = 'normal';

    /**
     * @var string
     */
    const SIZE_XL = 'xl';

    /**
     * @var string
     */
    const SIZE_LG = 'lg';

    /**
     * @var string
     */
    const SIZE_SM = 'sm';

    /**
     * @var string
     */
    protected $type = 'qc-modal';

    /**
     * setShow
     *
     * @param boolean $_show
     */
    public function setShow(bool $_show) : Modal
    {
        $this->options['show'] = $_show;
        return $this;
    }

    /**
     * Set modal type
     *
     * @param string $_type
     *
     * @return Modal
     */
    public function type(string $_type): Modal
    {
        $this->setOption('modal-type', $_type);
        return $this;
    }

    /**
     * Set modal size
     *
     * @param string $_size 
     *
     * @return Modal
     */
    public function size(string $_size): Modal
    {
        $this->setOption('size', $_size);
        return $this;
    }

    /**
     * Set control panel buttons
     *
     * @param array $_buttons 
     *
     * @return Modal 
     */
    public function panel(array $_buttons): Modal
    {
        $this->setOption('panel', $_buttons);
        return $this; 
    }

}
