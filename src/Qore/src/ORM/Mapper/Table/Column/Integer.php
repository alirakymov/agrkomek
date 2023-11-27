<?php

declare(strict_types=1);

namespace Qore\ORM\Mapper\Table\Column;

use Qore\ORM\Mapper;
use Laminas\Db\Sql\Ddl\Column\Integer as ZendInteger;

class Integer extends ZendInteger implements ColumnInterface
{
    use ColumnContract;
    use Mapper\Reference\ReferenceMapContract;
}
