<?php

namespace Qore\App\SynapseNodes\Components\Operation;

use Qore\ORM\Entity\EntityInterface;

abstract class OperationPhaseExecutor implements OperationPhaseExecutorInterface
{
    /**
     * @var \Qore\ORM\Entity\EntityInterface
     */
    private EntityInterface $target;

    /**
     * @var Operation
     */
    private Operation $operation;

    /**
     * @var array
     */
    private array $options;


    /**
     * Conscturctor
     *
     * @param \Qore\ORM\Entity\EntityInterface $_target
     */
    public function __construct(Operation $_operation, EntityInterface $_target, array $_options)
    {
        $this->target = $_target;
        $this->operation = $_operation;
        $this->options = $_options;
    }

    /**
     * Get target
     *
     * @return \Qore\ORM\Entity\EntityInterface
     */
    public function getTarget(): EntityInterface
    {
        return $this->target;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @inheritdoc
     */
    abstract public function execute(): bool;

}
