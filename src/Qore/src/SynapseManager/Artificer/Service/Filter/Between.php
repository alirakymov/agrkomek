<?php

namespace Qore\SynapseManager\Artificer\Service\Filter;

use Qore\ORM\Gateway\GatewayCursor;
use Qore\ORM\Mapper\Table\Column\Datetime;
use Qore\ORM\Mapper\Table\Column\Integer;
use Qore\SynapseManager\Artificer\Service\Filter;

class Between implements TypeInterface 
{
    use Utils;

    /**
     * @var string - value template string
     */
    private string $expression = '%s~%s';

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
        if (! is_array($_value) && ! is_string($_value)) {
            throw new TypeException(sprintf('For between filter type value must be an array or string %s given', gettype($_value)));
        }

        if (is_string($_value)) {
            if (! preg_match(sprintf('/^%s$/u', sprintf($this->expression, '(.*)', '(.*)')), $_value, $result)) {
                $_value = null;
                return;
            }

            $_value = [$result[1], $result[2]];
        }

        $_value = array_values($_value);

        $estimatedTypes = [ Datetime::class, Integer::class ];
        $this->value = [
            isset($_value[0]) ? $this->parseValue($_value[0], $estimatedTypes) : null, 
            isset($_value[1]) ? $this->parseValue($_value[1], $estimatedTypes) : null,
        ];
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
    public function valueToString(): string
    {
        return ! is_null($this->value) 
            ? sprintf(
                $this->expression, 
                $this->prepareValue($this->value[0]),
                $this->prepareValue($this->value[1])
            ) : '';
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
            $_where->between(
                $_attribute, 
                $this->value[0]->format('Y-m-d H:i'),
                $this->value[1]->format('Y-m-d H:i')
            );
        }

        return $_where;
    }

}
