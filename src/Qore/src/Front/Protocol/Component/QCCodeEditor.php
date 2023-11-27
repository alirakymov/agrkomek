<?php

namespace Qore\Front\Protocol\Component;

use Qore\Collection\Collection;
use Qore\Front\Protocol\BaseProtocol;

class QCCodeEditor extends BaseProtocol
{
    /**
     * type
     *
     * @var string
     */
    protected $type = 'qc-codeeditor';

    /**
     * setData
     *
     * @param mixed $_data
     */
    public function setData($_data)
    {
        if ($_data instanceof Collection) {
            $_data = $_data->toList();
        }

        $this->options['data'] = $_data;
        return $this;
    }

    /**
     * setTableOptions
     *
     * @param array $_options
     */
    public function addOptions(array $_options) : QCCodeEditor
    {
        foreach ($_options as $optionName => $option) {
            $this->setOption($optionName, $option);
        }

        return $this;
    }

}
