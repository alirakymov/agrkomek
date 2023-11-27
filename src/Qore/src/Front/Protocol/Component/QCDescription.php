<?php

namespace Qore\Front\Protocol\Component;

use Qore\Front\Protocol\BaseProtocol;

class QCDescription extends BaseProtocol
{
    /**
     * type
     *
     * @var string
     */
    protected $type = 'qc-description';

    /**
     * setTableData
     *
     * @param array $_columns
     * @param mixed $_model
     */
    public function setDescriptionData(array $_columns, $_model)
    {
        $this->options['columns'] = $this->prepareColumns($_columns);
        $this->options['descriptionData'] = $this->transformModel($_columns, $_model);

        return $this;
    }

    /**
     * prepareColumns
     *
     * @param mixed $_columns
     */
    private function prepareColumns($_columns)
    {
        foreach ($_columns as &$column) {
            if (isset($column['transform'])) {
                unset($column['transform']);
            }
        }

        return $_columns;
    }

    /**
     * transformModel
     *
     * @param mixed $_columns
     * @param mixed $_model
     */
    private function transformModel($_columns, $_model)
    {
        $return = [];

        foreach ($_columns as $columnName => $columnOptions) {
            if (isset($columnOptions['transform'])) {
                $return[$columnName] = $columnOptions['transform']($_model);
            } else {
                $return[$columnName] = $this->getValueByPath($_model, $columnOptions['model-path'] ?? $columnName, $columnOptions['default'] ?? '');
            }
        }

        return $return;
    }

    /**
     * getValueByPath
     *
     * @param mixed $_item
     * @param mixed $_path
     * @param string $_default
     */
    private function getValueByPath($_item, $_path, $_default = '')
    {
        $_path = explode('.', $_path);

        foreach ($_path as $pathKey) {
            if (isset($_item[$pathKey])) {
                $_item = $_item[$pathKey];
            } else {
                return $_default;
            }
        }

        return $_item;
    }
}
