<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\Operation;

interface OperationPhaseExecutorInterface
{
    /**
     * Execute phase code
     *
     * @return bool
     */
    public function execute(): bool;

}
