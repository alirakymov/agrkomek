<?php

namespace Qore\Front\Protocol\Component;

use Qore\Front\Protocol\BaseProtocol;

class QCModal extends BaseProtocol
{
    /**
     * type
     *
     * @var string
     */
    protected $type = 'qc-modal';

    /**
     * setShow
     *
     * @param boolean $_show
     */
    public function setShow(bool $_show)
    {
        $this->options['show'] = $_show;
        return $this;
    }
}
