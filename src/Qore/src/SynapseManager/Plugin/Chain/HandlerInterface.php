<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Plugin\Chain;

use Qore\DealingManager\ResultInterface;
use Qore\DealingManager\ScenarioInterface;

interface HandlerInterface
{
    /**
     * handle node of chain
     *
     * @param ModelInterface $_model
     * @param ChainProcessor $_next
     *
     * @return
     */
    public function handle($_model, ScenarioInterface $_next) : ResultInterface;
}
