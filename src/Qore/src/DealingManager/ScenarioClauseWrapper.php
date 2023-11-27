<?php


declare(strict_types=1);

namespace Qore\DealingManager;

/**
 * Class: ScenarioClauseWrapper
 *
 * @see ScenarioClauseWrapperInterface
 */
class ScenarioClauseWrapper implements ScenarioClauseWrapperInterface
{
    /**
     * scenarioClause
     *
     * @var mixed
     */
    private $scenarioClause = null;

    /**
     * @var mixed 
     */
    private $identifier;

    /**
     * __construct
     *
     */
    public function __construct(ScenarioClauseInterface $_clause)
    {
        $this->scenarioClause = $_clause;
    }

    /**
     * setIdentifier
     *
     * @param mixed $_identifier
     */
    public function setIdentifier($_identifier) : void
    {
        $this->identifier = $_identifier;
    }

    /**
     * getIdentifier
     *
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * getClause
     *
     */
    public function getClause() : ScenarioClauseInterface
    {
        return $this->scenarioClause;
    }

}
