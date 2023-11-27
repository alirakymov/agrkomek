<?php

namespace Qore\Front\Protocol\Component;

use Qore\Front\Protocol\BaseProtocol;

class QCSimpleTile extends BaseProtocol
{
    /**
     * type
     *
     * @var string
     */
    protected $type = 'qc-simpletile';

    /**
     * setTileData
     *
     * @param array $_data
     */
    public function setTileData(array $_data)
    {
        $this->options['tileData'] = $_data;
        return $this;
    }
}
