<?php

declare(strict_types=1);

namespace Qore\ORM\Sql;

use Laminas\Db\Sql\Select as SqlSelect;

class Select extends SqlSelect
{
    use CursorTrait;

    /**
     * @var bool - auto columns flag
     */
    private bool $autoColumns = true;

    /**
     * @inheritdoc
     */
    public function columns(array $columns, $prefixColumnsWithTable = true, $_autoColumns = true)
    {
        parent::columns($columns, $prefixColumnsWithTable);
        $this->autoColumns = $_autoColumns;
        return $this;
    }

    /**
     * Retrive auto columns flag
     *
     * @return bool
     */
    public function withAutoColumns(): bool
    {
        return $this->autoColumns;
    }

    /**
     * Retrive property by name
     *
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        switch (strtolower($name)) {
            case 'columns':
                return $this->columns;
            default:
                return parent::__get($name);
        }
    }

}
