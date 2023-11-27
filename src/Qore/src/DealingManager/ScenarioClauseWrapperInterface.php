<?php


declare(strict_types=1);

namespace Qore\DealingManager;

interface ScenarioClauseWrapperInterface
{
    /**
     * setIdentifier
     *
     * @param mixed $_identifier
     */
    public function setIdentifier($_identifier) : void;

    /**
     * getIdentifier
     *
     */
    public function getIdentifier();

    /**
     * getClause
     *
     */
    public function getClause() : ScenarioClauseInterface;
}
