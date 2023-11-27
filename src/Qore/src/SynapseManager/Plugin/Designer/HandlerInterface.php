<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Plugin\Designer;

use Qore\DealingManager\ResultInterface;
use Qore\DealingManager\ScenarioInterface;
use Qore\SynapseManager\Plugin\Chain\HandlerInterface as ChainHandlerInterface;

interface HandlerInterface extends ChainHandlerInterface
{
    /**
     * Handle node of chain
     *
     * @param \Qore\SynapseManager\Plugin\Designer\ModelInterface $_model
     * @param \Qore\DealingManager\ScenarioInterface $_next
     *
     * @return \Qore\DealingManager\ResultInterface
     */
    public function handle($_model, ScenarioInterface $_next) : ResultInterface;

}
