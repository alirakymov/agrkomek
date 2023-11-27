<?php

namespace Qore\InterfaceGateway\Component\Grid;

use Qore\InterfaceGateway\Component\AbstractComponent;

class Row extends AbstractComponent
{
    /**
     * @var string
     */
    protected $type = 'qcg-row';

    /**
     * @var array<Column>
     */
    protected array $columns = [];

    /**
     * Get column with initialized class column size
     *
     * @param int $_size
     * @param string|int|null $_name (optional)
     *
     * @return Column
     */
    public function colx(int $_size, $_name = null) : Column
    {
        return $this->column($_name)->setClass(sprintf('col-%s', $_size));
    }

    /**
     * Return (generate it if is absent) column component
     *
     * @param string|int|null $_name (optional)
     *
     * @return Column
     */
    public function column($_name = null) : Column
    {
        $_name = $this->getColumnName($_name);
        return $this->columns[$_name] ??= ($this->ig)(Column::class, $_name)->setOption('parent', $this->name);
    }

    /**
     * Set class option
     *
     * @param string $_class
     *
     * @return Row
     */
    public function setClass(string $_class) : Row
    {
        $this->setOption('style-class', $_class);
        return $this;
    }

    /**
     * Get unique component name for Column component
     *
     * @param string|int|null $_name (optional)
     *
     * @return string
     */
    protected function getColumnName($_name = null) : string
    {
        return sprintf('%s.%s', $this->getName(), $_name ?? count($this->columns));
    }

    /**
     * Compose tabs to array
     *
     * @return array
     */
    public function compose(): array
    {
        $this->components = array_values($this->columns);
        return parent::compose();
    }

}
