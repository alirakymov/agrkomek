<?php

namespace Qore\App\SynapseNodes\Components\Operation;

use Generator;

interface OperationPhasesProviderInterface
{
    /**
     * Load phases files
     *
     * @return \Generator 
     */
    public function getPhases(): Generator;
}
