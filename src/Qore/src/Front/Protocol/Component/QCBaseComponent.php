<?php

namespace Qore\Front\Protocol\Component;

use Qore\Front\Protocol\BaseProtocol;

class QCBaseComponent extends BaseProtocol
{
    /**
     * type
     *
     * @var string
     */
    protected $type = 'qc-component';

    /**
     * __construct
     *
     * @param string $_name
     */
    public function __construct(string $_name)
    {
        parent::__construct($_name);
    }

}
