<?php

declare(strict_types=1);

namespace Qore\DealingManager;

use Qore\Collection\Collection;

interface ScenarioInterface
{
    /**
     * process
     *
     * @param mixed $_model
     */
    public function process($_model) : ResultInterface;

    /**
     * getSubScenario
     *
     */
    public function getSubScenario() : ScenarioInterface;

    /**
     * pipe
     *
     * @param ScenarioClauseInterface $_clause
     */
    public function pipe(ScenarioClauseWrapperInterface $_clause) : void;
}
