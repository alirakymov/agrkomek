<?php

namespace Qore\Front\Protocol\Component;

use Qore\Front\Protocol\BaseProtocol;

class QCGrid extends BaseProtocol
{
    /**
     * type
     *
     * @var string
     */
    protected $type = 'qc-grid';

    /**
     * rows
     *
     * @var array
     */
    protected $rows = [];
    /**
     * row
     *
     * @param \Closure $_row
     */
    public function row(\Closure $_row)
    {
        $columnObject = new QCGrid\QCGridColumn();

        $key = &$columnObject;
        $_row($key);

        $rowColumns = [];
        foreach ($columnObject->getColumns() as $column) {
            $rowColumns[] = [
                'class' => $column['class'],
                'component' => $column['component'],
            ];
        }

        unset($columnObject);
        $this->rows[] = $rowColumns;

        return $this;
    }

    /**
     * asArray
     *
     */
    public function asArray()
    {
        $this->options['rows'] = $this->rows;

        return parent::asArray();
    }
}
