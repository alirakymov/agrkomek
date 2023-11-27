<?php

namespace Qore\SynapseManager\Plugin\Operation;

use Qore\DealingManager\ResultInterface;

interface HandlerInterface
{
    /**
     * Set operation
     *
     * @param OperationInterface $_operation
     *
     * @return PhaseInterface
     */
    public function setOperation(OperationInterface $_operation): PhaseInterface;

    /**
     * Handle phase
     *
     * @param ModelInterface $_model
     *
     * @return ?ResultInterface
     */
    public function handle(ModelInterface $_model) : ?ResultInterface;

    /**
     * Generate Result instance
     *
     * @return \Qore\DealingManager\ResultInterface
     */
    public function result(...$_result): ResultInterface;

    /**
     * Create task and push it to queue TaskProcess
     *
     * @param \Closure $_closure
     * @param string $_suffix (optional)
     *
     * @return TaskInterface
     */
    public function defer(\Closure $_closure, string $_suffix = null) : TaskInterface;

    /**
     * Return phase identifier
     *
     * @return string
     */
    public function getIdentifier(): string;

    /**
     * Register current phase as processed and go to the next phase
     *
     * @return \Qore\DealingManager\ResultInterface|null
     */
    public function next(): ?ResultInterface;

}
