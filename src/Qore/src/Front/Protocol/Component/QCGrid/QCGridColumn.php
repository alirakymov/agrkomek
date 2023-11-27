<?php

namespace Qore\Front\Protocol\Component\QCGrid;

use Qore\Front\Protocol\BaseProtocol;

class QCGridColumn
{
    /**
     * columns
     *
     * @var mixed
     */
    private $columns = [];

    /**
     * __call
     *
     * @param mixed $_name
     * @param mixed $_arguments
     */
    public function __call($_name, $_arguments)
    {
        $class = '';

        if (preg_match('/col([0-9]{1,2})/', $_name, $matches)) {
            $class = 'col-sm-' . $matches[1][0];
        }

        if (isset($_arguments[1])) {
            $class .= ' ' . $_arguments[1];
        }

        return $this->column($_arguments[0], $class);
    }

    /**
     * column
     *
     * @param mixed $_section
     */
    public function column($_component, $_class)
    {
        $this->columns[] = [
            'component' => $_component->asArray(),
            'class' => $_class,
        ];
        return $this;
    }

    /**
     * getColumns
     *
     */
    public function getColumns()
    {
        return $this->columns;
    }
}
