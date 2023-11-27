<?php

namespace Qore\Front\Protocol\Component;

use Qore\Front\Protocol\BaseProtocol;

class QCHtmlContent extends BaseProtocol
{
    /**
     * type
     *
     * @var string
     */
    protected $type = 'qc-htmlcontent';

    /**
     * setData
     *
     * @param mixed $_data
     */
    public function setData($_data)
    {
        $this->options['data'] = $_data;
        return $this;
    }

}
