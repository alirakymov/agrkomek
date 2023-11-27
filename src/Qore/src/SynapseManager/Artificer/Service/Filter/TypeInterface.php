<?php

namespace Qore\SynapseManager\Artificer\Service\Filter;

use Qore\ORM\Gateway\GatewayCursor;

interface TypeInterface
{
    /**
     * Get filter type value
     *
     * @return mixed
     */
    public function getValue();

    /**
     * Convert value to string
     *
     * @return mixed
     */
    public function valueToString();

    /**
     * Get filter type suffix
     *
     * @return string 
     */
    public function getTypeSuffix(): string;

    /**
     * Apply filters
     *
     * @param \Qore\ORM\Gateway\GatewayCursor $_where 
     * @param string $_attribute
     *
     * @return \Qore\ORM\Gateway\GatewayCursor
     */
    public function apply(GatewayCursor $_where, string $_attribute): GatewayCursor;

}
