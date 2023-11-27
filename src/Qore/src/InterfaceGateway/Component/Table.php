<?php

declare(strict_types=1);

namespace Qore\InterfaceGateway\Component;

class Table extends AbstractComponent
{
    /**
     * type
     *
     * @var string
     */
    protected $type = 'qc-table';

    /**
     * setTableData
     *
     * @param array $_columns
     * @param mixed $_model
     */
    public function setTableData(array $_columns, $_model)
    {
        $this->options['columns'] = $this->prepareColumns($_columns);
        $this->options['tableData'] = $this->transformModel($_columns, $_model);
        return $this;
    }

    /**
     * Set in-block option for table component
     *
     * @param bool $_value (optional)
     *
     * @return Table
     */
    public function inBlock(bool $_value = true) : Table
    {
        $this->options['in-block'] = $_value;
        return $this;
    }

    /**
     * setBreadcrumbs
     *
     * @param array $_breadcrumbs
     */
    public function setBreadcrumbs(array $_breadcrumbs)
    {
        $this->options['breadcrumbs'] = $_breadcrumbs;
        return $this;
    }

    /**
     * setTableOptions
     *
     * @param array $_options
     */
    public function setTableOptions(array $_options) : Table
    {
        $this->options = array_merge($this->options, $_options);
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
        foreach ($_model as $item) {
            $row = [];
            foreach ($_columns as $columnName => $columnOptions) {
                if ($columnName !== 'table-actions') {
                    if (isset($columnOptions['transform'])) {
                        $row[$columnName] = $this->applyTransform($columnOptions['transform'], $item);
                    } else {
                        $row[$columnName] = $this->getValueByPath($item, $columnOptions['model-path'] ?? $columnName, $columnOptions['default'] ?? '');
                    }
                } else {
                    $rowActions = [];
                    foreach ($columnOptions['actions'] as $actionName => $actionOptions) {
                        $options = [];
                        foreach ($actionOptions as $optionName => $optionValue) {
                            $options[$optionName] = is_callable($optionValue) ? $optionValue($item) : $optionValue;
                        }
                        $rowActions[$actionName] = $options;
                    }
                    $row['actions'] = $rowActions;
                }
            }
            $return[] = $row;
        }

        return $return;
    }

    /**
     * applyTransform
     *
     * @param mixed $_transform
     * @param mixed $_item
     */
    private function applyTransform($_transform, $_item)
    {
        $result = $_item;
        if (!is_array($_transform)) {
            $_transform = [$_transform];
        }

        foreach ($_transform as $callback) {
            $result = $callback($result);
        }
        return $result;
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
