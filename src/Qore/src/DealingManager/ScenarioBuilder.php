<?php

namespace Qore\DealingManager;

/**
 * Class: ScenarioBuilder
 *
 */
class ScenarioBuilder
{
    /**
     * scenario
     *
     * @var mixed
     */
    protected $scenario = null;

    /**
     * __construct
     *
     * @param ScenarioInterfcae $_scenario
     */
    public function __construct()
    {
    }

    /**
     * __invoke
     *
     * @param mixed $_scenario
     * @param callable $_group
     */
    public function __invoke($_scenarioClause, callable $_group = null)
    {
        $this->scenario->pipe($this->scenario->marshalScenarioClause($_scenarioClause));

        if (! is_null($_group)) {
            $currentScenario = $this->scenario;
            $this->scenario = $this->scenario->getSubScenario();
            $_group($this);
            $this->scenario = $currentScenario;
        }
    }

    /**
     * set scenario
     *
     * @param ScenarioInterface $_scenario
     */
    public function setScenario(ScenarioInterface $_scenario) : ScenarioBuilder
    {
        $this->scenario = $_scenario;
        return $this;
    }

    /**
     * return scenario
     *
     * @return ScenarioInterface
     */
    public function getScenario() : ScenarioInterface
    {
        return $this->scenario;
    }

}
