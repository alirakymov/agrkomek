<?php

namespace Qore\Front\Protocol\Component;

use Qore\Front\Protocol\BaseProtocol;

class QCInfoBlock extends BaseProtocol
{
    const TYPE_INFO = 'info';
    const TYPE_SUCCESS = 'success';
    const TYPE_WARNING = 'warning';
    const TYPE_ERROR = 'error';

    /**
     * type
     *
     * @var string
     */
    protected $type = 'qc-infoblock';

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

    /**
     * setInfoType
     *
     * @param mixed $_type
     */
    public function setInfoType($_type)
    {
        $this->options['info-type'] = $_type;
        return $this;
    }

}
