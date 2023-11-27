<?php

declare(strict_types=1);

namespace Qore\ORM\Mapper\Table\Column;

use Qore\ORM\Mapper;
use Laminas\Db\Sql\Ddl\Column\Datetime as ZendDatetime;

class Datetime extends ZendDatetime implements ColumnInterface
{
    use ColumnContract;
    use Mapper\Reference\ReferenceMapContract;
}
