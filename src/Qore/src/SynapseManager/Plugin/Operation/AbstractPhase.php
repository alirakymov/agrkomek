<?php

namespace Qore\SynapseManager\Plugin\Operation;

use Closure;
use Qore\DealingManager\Result;
use Qore\DealingManager\ResultInterface;

abstract class AbstractPhase implements PhaseInterface
{
    /**
     * @var string - index for global (operation) state in states container
     */
    const global = 'operation-state';

    /**
     * @var OperationInterface
     */
    protected OperationInterface $_operation;

    /**
     * @var string|int
     */
    protected $_index;

    /**
     * @var ?string - class name (used on task dispatching)
     */
    protected ?string $_class;

    /**
     * @var ModelInterface - instance of model
     */
    protected ModelInterface $_model;

    /**
     * Constructor
     *
     * @param $_index
     * @param string $_class (optional)
     */
    public function __construct($_index, string $_class = null)
    {
        $this->_index = $_index;
        $this->_class = $_class;
    }

    /**
     * @inheritdoc
     */
    public function setOperation(OperationInterface $_operation): PhaseInterface
    {
        $this->_operation = $_operation;
        return $this;
    }

    /**
     * @inheritdoc
     */
    final public function handle(ModelInterface $_model): ?ResultInterface
    {
        $this->_model = $_model;

        if ($_model->isInitialize()) {
            $this->initialize($_model);
            return new Result();
        }

        if ($_model->isProcess()) {
            return $this->process($_model);
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function defer(Closure $_closure, string $_suffix = null): TaskInterface
    {
        return $this->_operation->defer(
            $_closure,
            $this->getIdentifier(),
            $this->_class ?? static::class,
            $_suffix
        );
    }

    /**
     * @inheritdoc
     */
    public function result(...$_result): ResultInterface
    {
        return new Result($_result);
    }

    /**
     * Get phase identifier, it is used as index of phase state in states storage
     *
     * @return string
     */
    public function getIdentifier(): string
    {
        return sprintf('%s#%s', $this->_class ?? static::class, $this->_index);
    }

    /**
     * @inheritdoc
     */
    public function next(): ?ResultInterface
    {
        return $this->_model->next($this);
    }

}
