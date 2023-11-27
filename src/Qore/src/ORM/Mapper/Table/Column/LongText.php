<?php

declare(strict_types=1);

namespace Qore\ORM\Mapper\Table\Column;

use Qore\ORM\Mapper;
use Laminas\Db\Sql\Ddl\Column\Text as LaminasText;

class LongText extends LaminasText implements ColumnInterface
{
    use ColumnContract;
    use Mapper\Reference\ReferenceMapContract;

    /**
     * @var string
     */
    protected $type = 'LONGTEXT';
}
