<?php

namespace Qore\App\SynapseNodes\Components\Operation;

use Qore\ORM\Entity\EntityInterface;

interface OperationConstructorInterface
{
    /**
     * Build operation thread
     *
     * @param Operation $_operation 
     * @param \Qore\ORM\Entity\EntityInterface $_target 
     * @param array $_options (optional)
     *
     * @return OperationRuntime|null 
     */
    public function build(Operation $_operation, EntityInterface $_target, array $_options = []): ?OperationRuntime;

}
