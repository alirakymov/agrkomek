<?php

declare(strict_types=1);

namespace Qore\ORM\Mapper\Table\Column;

use Qore\ORM\Mapper;
use Laminas\Db\Sql\Ddl\Column\BigInteger as ZendBigInteger;

class BigInteger extends ZendBigInteger implements ColumnInterface
{
    use ColumnContract;
    use Mapper\Reference\ReferenceMapContract;
}
