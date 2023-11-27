<?php

declare(strict_types=1);

namespace Qore\DealingManager;

use Qore\Collection\Collection;
use function sprintf;

/**
 * Class: Scenario
 *
 * @see ScenarioInterface
 */
class Scenario implements ScenarioInterface
{
    /**
     * @var mixed SplQueue
     */
    private $pipeline;

    /**
     * subScenarios
     *
     * @var mixed
     */
    private $subScenarios = [];

    /**
     * scenarioClauseFactory
     *
     * @var mixed
     */
    private $scenarioClauseFactory = null;

    /**
     * isProcessed
     *
     * @var mixed
     */
    private $isProcessed = false;

    /**
     * __construct
     *
     * @param ScenarioClauseFactory $_scenarioClauseFactory
     */
    public function __construct(ScenarioClauseFactory $_scenarioClauseFactory)
    {
        $this->scenarioClauseFactory = $_scenarioClauseFactory;
        $this->pipeline = new \SplQueue();
    }

    /**
     * __clone
     *
     */
    public function __clone()
    {
        $this->pipeline = clone $this->pipeline;
    }

    /**
     * process
     *
     * @param DealingModelInterface $model
     */
    public function process($model) : ResultInterface
    {
        $result = new Result([]);

        if ($this->pipeline->isEmpty()) {
            $this->isProcessed = true;
            return $result;
        }

        $nextHandler = clone $this;
        $clauseWrapper = $nextHandler->pipeline->dequeue();

        if (isset($this->subScenarios[$clauseWrapper->getIdentifier()])) {
            $subNextHandler = clone $this->subScenarios[$clauseWrapper->getIdentifier()];
            $result->merge($clauseWrapper->getClause()->processClause($model, $subNextHandler));
            if (! $nextHandler->pipeline->isEmpty()) {
                // $clauseWrapper = $nextHandler->pipeline->dequeue();
                $result->merge($nextHandler->process($model));
            }
        } else {
            $result->merge($clauseWrapper->getClause()->processClause($model, $nextHandler));
        }

        if ($nextHandler->isProcessed()) {
            $this->isProcessed = true;
        }

        return $result;
    }

    /**
     * isProcessed
     *
     */
    public function isProcessed() : bool
    {
        return $this->isProcessed;
    }

    /**
     * pipe
     *
     * @param ScenarioClauseInterface|ScenarioClauseWrapperInterface $_clause
     */
    public function pipe($_clause) : void
    {
        # - Wrap scenario clause if is not in wrapper
        if ($_clause instanceof ScenarioClauseInterface && ! $_clause instanceof ScenarioClauseWrapperInterface) {
            $_clause = $this->marshalScenarioClause($_clause);
        }

        # - Check clause
        if (! is_object($_clause) || ! $_clause instanceof ScenarioClauseWrapperInterface) {
            throw new Exception\ScenarioClauseException(sprintf(
                'Scenario clause object (%s) is must be an instance of %s or %s',
                get_class($_clause),
                ScenarioInterface::class,
                ScenarioClauseInterface::class
            ));
        }

        # - Set clause identifier to wrapper
        $_clause->setIdentifier($this->getCurrentClauseIndentifier());
        # - Add to queue
        $this->pipeline->enqueue($_clause);
    }

    /**
     * getSubScenario
     *
     */
    public function getSubScenario() : ScenarioInterface
    {
        $identifier = $this->pipeline->top()->getIdentifier();
        if (! isset($this->subScenarios[$identifier])) {
            $this->subScenarios[$identifier] = new static($this->scenarioClauseFactory);
        }
        return $this->subScenarios[$identifier];
    }

    /**
     * getClauseIndentifier
     *
     */
    public function getCurrentClauseIndentifier()
    {
        return 'clause-' . $this->pipeline->count();
    }

    /**
     * marshalScenarioClause
     *
     */
    public function marshalScenarioClause($_clause)
    {
        $scenarioClauseFactory = $this->scenarioClauseFactory;
        return $scenarioClauseFactory($_clause);
    }

    /**
     * Reset pipeline
     *
     * @return Scenario
     */
    public function reset() : Scenario
    {
        $this->pipeline = new \SplQueue();
        return $this;
    }
}
