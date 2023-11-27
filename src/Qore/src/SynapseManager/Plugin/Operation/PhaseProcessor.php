<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Plugin\Operation;

use Closure;
use Opis\Closure\SerializableClosure;
use Qore\DealingManager\ResultInterface;
use Qore\DealingManager\ScenarioClauseInterface;
use Qore\DealingManager\ScenarioInterface;

class PhaseProcessor implements ScenarioClauseInterface
{

    /**
     * @var PhaseInterface
     */
    protected PhaseInterface $_phase;

    /**
     * @var Operation
     */
    protected Operation $_operation;

    /**
     * Constructor
     *
     * @param PhaseInterface $_phase
     */
    public function __construct(PhaseInterface $_phase, Operation $_operation)
    {
        $this->_phase = $_phase;
        $this->_phase->setOperation($_operation);
        $this->_operation = $_operation;
    }

    /**
     * Process building mapping
     *
     * @param $_model
     * @param \Qore\DealingManager\ScenarioInterface $_next
     *
     * @return \Qore\DealingManager\ResultInterface
     */
    public function processClause($_model, ScenarioInterface $_next) : ResultInterface
    {
        $_model->setNext($_next);
        return $this->wrapEnvironment($_model, function($_model) {
            return $this->_phase->handle($_model);
        });
    }

    /**
     * Initialize environment for this chain
     *
     * @param ModelInterface $_model
     * @param \Closure $_closure
     *
     * @return \Qore\DealingManager\ResultInterface
     */
    protected function wrapEnvironment(ModelInterface $_model, \Closure $_closure) : ResultInterface
    {
        # - before launch phase
        $result = $_closure($_model);
        # - after launch phase
        return $result;
    }

}
