<?php

namespace Qore\SynapseManager\Artificer\Service;

use Closure;
use Qore\ORM\Gateway\GatewayCursor;
use Qore\SynapseManager\Artificer\Service\Filter\Between;
use Qore\SynapseManager\Artificer\Service\Filter\Equal;
use Qore\SynapseManager\Artificer\Service\Filter\In;
use Qore\SynapseManager\Artificer\Service\Filter\TypeInterface;

class Filter
{
    /**
     * @var array - filter types collection
     */
    protected static array $types = [
        'b' => Between::class,
        'e' => Equal::class,
        'i' => In::class,
    ];

    /**
     * @var string
     */
    protected string $defaultType = Equal::class;

    /**
     * @var TypeInterface - instance of type
     */
    protected TypeInterface $typeInstance;

    /**
     * Construct
     *
     * @param mixed $_value 
     * @param string|null $_type 
     */
    public function __construct($_value, ?string $_type = null)
    {
        $class = in_array($_type, self::$types) 
            ? $_type 
            : (self::$types[$_type] ?? $this->defaultType);

        $this->typeInstance = new $class($_value);
    }

    /**
     * Apply type instance
     *
     * @param \Qore\ORM\Gateway\GatewayCursor $_where 
     * @param string $_attribute 
     *
     * @return \Qore\ORM\Gateway\GatewayCursor
     */
    public function __invoke(GatewayCursor $_where, string $_attribute): GatewayCursor
    {
        return $this->typeInstance->apply($_where, $_attribute);
    }

    /** Get type instance
     *
     * @return \Qore\SynapseManager\Artificer\Service\Filter\TypeInterface
     */
    public function getTypeInstance(): TypeInterface
    {
        return $this->typeInstance;
    }

    /**
     * Apply filters
     *
     * @param \Closure $_callback
     *
     * @return mixed
     */
    public function apply(\Closure $_callback)
    {
        return $_callback($this->typeInstance->getValue(), $this->typeInstance);
    }

    /**
     * Get type suffix
     *
     * @param string $_class 
     *
     * @return string|null
     */
    public static function getTypeSuffix(string $_class): ?string
    {
        return array_search($_class, self::$types) ?: null;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->typeInstance->valueToString();
    }

}
