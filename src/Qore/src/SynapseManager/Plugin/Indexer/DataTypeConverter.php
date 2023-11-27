<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Plugin\Indexer;

use Qore\ORM\Mapper\Table\Column\BigInteger;
use Qore\ORM\Mapper\Table\Column\Datetime;
use Qore\ORM\Mapper\Table\Column\Decimal;
use Qore\ORM\Mapper\Table\Column\Integer;
use Qore\ORM\Mapper\Table\Column\LongText;
use Qore\ORM\Mapper\Table\Column\Text;
use Qore\ORM\Mapper\Table\Column\Varchar;

class DataTypeConverter implements DataTypeConverterInterface
{
    /**
     * @var array
     */
    private $_types;

    /**
     * Constructor
     *
     * @param array $_types
     */
    public function __construct(array $_types)
    {
        $this->_types = $_types;
    }

    /**
     * @inheritdoc
     */
    public function convertValue(string $_type, $_value) 
    {
        if (! isset($this->_types[$_type])) {
            throw new PluginException(sprintf('Undefined type %s on mapping', $_type));
        }

        switch (true) {
            case $_type === Integer::class:
            case $_type === BigInteger::class:
                return (int)$_value;
            case $_type === Datetime::class:
                return is_object($_value) ? $_value->getTimestamp() : (int)$_value;
            case $_type === Decimal::class:
                return (float)$_value;
        }

        return $_value;
    }

    /**
     * @inheritdoc
     */
    public function convert(string $_type): array
    {
        if (! isset($this->_types[$_type])) {
            throw new PluginException(sprintf('Undefined type %s on mapping', $_type));
        }

        return $this->_types[$_type];
    }

}
