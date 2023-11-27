<?php

namespace Qore\InterfaceGateway\Component;

/**
 * Class: Tile
 *
 * @see BaseProtocol
 */
class Tile extends AbstractComponent
{
    /**
     * type
     *
     * @var string
     */
    protected $type = 'qc-tile';

    /**
     * __construct
     *
     * @param string $_name
     */
    public function __construct(string $_name)
    {
        parent::__construct($_name);
    }

    /**
     * setTileData
     *
     * @param array $_options
     * @param mixed $_model
     */
    public function setTileData(array $_options, $_model)
    {
        $this->options['options'] = $_options;
        $this->options['tileData'] = $this->transformModel($_options, $_model);
        return $this;
    }

    /**
     * setTableOptions
     *
     * @param array $_options
     */
    public function setTileOptions(array $_options) : Tile
    {
        foreach ($_options as $optionName => $option) {
            $this->setOption($optionName, $option);
        }

        return $this;
    }

    /**
     * isDraggable
     *
     * @param mixed $_draggable
     */
    public function isDraggable($_draggable = true)
    {
        $this->setOption('draggable', $_draggable);
        return $this;
    }

    /**
     * transformModel
     *
     * @param mixed $_columns
     * @param mixed $_model
     */
    private function transformModel($_options, $_model)
    {
        $return = [];

        foreach ($_model as $item) {

            $tile = [];
            foreach ($_options as $optionName => $optionData) {
                if ($optionName !== 'tile-actions') {
                    $tile[$optionName] = $optionData($item);
                } else {
                    $tileActions = [];
                    foreach ($optionData as $actionName => $actionOptions) {
                        $options = [];
                        foreach ($actionOptions as $optionName => $optionValue) {
                            $options[$optionName] = is_callable($optionValue) ? $optionValue($item) : $optionValue;
                        }
                        $tileActions[$actionName] = $options;
                    }
                    $tile['actions'] = $tileActions;
                }
            }

            $return[] = $tile;
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
