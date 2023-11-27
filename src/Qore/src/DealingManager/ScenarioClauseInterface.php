<?php

namespace Qore\DealingManager;

use Qore\Collection\Collection;

interface ScenarioClauseInterface
{
    /**
     * processClause
     *
     * @param mixed $_model
     * @param ScenarioInterface $_nextHandler
     */
    public function processClause($_model, ScenarioInterface $_nextHandler) : ResultInterface;
}
