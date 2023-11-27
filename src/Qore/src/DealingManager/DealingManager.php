<?php

declare(strict_types=1);

namespace Qore\DealingManager;

use Qore\Qore;

class DealingManager
{
    /**
     * model
     *
     * @var mixed
     */
    private $model = null;

    /**
     * buider
     *
     * @var mixed
     */
    private $builder = null;

    /**
     * scenario
     *
     * @var mixed
     */
    private $scenario;

    /**
     * clauseFactory
     *
     * @var mixed
     */
    private $clauseFactory;

    /**
     * resultClass
     *
     * @var mixed
     */
    private $resultClass = Result::class;

    /**
     * __construct
     *
     * @param ScenarioBuilder $_builder
     */
    public function __construct(
        ScenarioInterface $_scenario,
        ScenarioBuilder $_builder
    ) {
        $this->scenario = $_scenario;
        $this->builder = $_builder;
    }

    /**
     * __invoke
     *
     * @param Callable $_build
     */
    public function __invoke(Callable $_build)
    {
        $_build($this->builder->setScenario(clone $this->scenario));
        return $this;
    }

    /**
     * setModel
     *
     * @param mixed $_model
     */
    public function setModel($_model = null)
    {
        $this->model = $_model ?? new Model();
        return $this;
    }

    /**
     * launch
     *
     */
    public function launch($_model = null)
    {
        is_null($_model) || $this->setModel($_model);
        return $this->builder->getScenario()->process($this->model);
    }

    /**
     * process
     *
     * @param mixed $_model
     */
    public function process($_model = null)
    {
        return $this->launch($_model);
    }

    /**
     * scenarioClauseFactory
     *
     * @param mixed $_scenarioClause
     */
    public function scenarioClauseFactory($_scenarioClause) : ScenarioClauseWrapperInterface
    {
        $clauseFactory = $this->clauseFactory;
        return $clauseFactory($_scenarioClause);
    }

    /**
     * Get scenario
     *
     * @return ScenarioInterface
     */
    public function getScenario() : ScenarioInterface
    {
        return $this->scenario;
    }
}
