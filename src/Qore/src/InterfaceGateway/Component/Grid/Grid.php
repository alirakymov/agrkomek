<?php

namespace Qore\InterfaceGateway\Component\Grid;

use Qore\InterfaceGateway\Component\AbstractComponent;

class Grid extends AbstractComponent
{
    /**
     * @var string
     */
    protected $type = 'qc-grid';

    /**
     * @var array<Row>
     */
    protected array $rows = [];

    /**
     * Return (generate it if is absent) row component
     *
     * @param string|int|null $_name
     *
     * @return Tab
     */
    public function row($_name = null) : Row
    {
        $_name = $this->getRowName($_name);
        return $this->rows[$_name] ??= ($this->ig)(Row::class, $_name)->setOption('parent', $this->name);
    }

    /**
     * Get unique component name for Row component
     *
     * @param string|int|null $_name (optional)
     *
     * @return string
     */
    protected function getRowName($_name = null) : string
    {
        return sprintf('%s.%s', $this->getName(), $_name ?? count($this->rows));
    }

    /**
     * Compose tabs to array
     *
     * @return array
     */
    public function compose(): array
    {
        $this->components = array_values($this->rows);
        return parent::compose();
    }

}
