<?php

namespace Qore\SynapseManager\Artificer\Service\Filter;

use Qore\ORM\Gateway\GatewayCursor;
use Qore\ORM\Mapper\Table\Column\Integer;
use Qore\SynapseManager\Artificer\Service\Filter;

class In implements TypeInterface
{
    use Utils;

    /**
     * @var string - value template string
     */
    private string $expression = '%s';

    /**
     * @var mixed
     */
    private $value = null;

    /**
     * Construct
     *
     * @param mixed $_value 
     */
    public function __construct($_value)
    {
        $this->value = $_value;
    }

    /**
     * @inheritdoc
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @inheritdoc
     */
    public function valueToString()
    {
        return $this->value;
    }

    /**
     * Get filter type suffix
     *
     * @return string 
     */
    public function nameToString(): string
    {
        return Filter::getTypeSuffix(static::class) ?? '';
    }

    /**
     * @inheritdoc
     */
    public function getTypeSuffix(): string
    {
        return Filter::getTypeSuffix(static::class) ?? '';
    }

    /**
     * @inheritdoc
     */
    public function apply(GatewayCursor $_where, string $_attribute): GatewayCursor
    {
        if (! is_null($this->value)) {
            $_where([$_attribute => $this->value]);
        }

        return $_where;
    }

}
