<?php

namespace Qore\DealingManager;

/**
 * Class: ScenarioClauseFactory
 *
 */
class ScenarioClauseFactory
{
    /**
     * __invoke
     *
     * @param mixed $_clauseClass
     */
    public function __invoke($_clause) : ScenarioClauseWrapperInterface
    {
        $clauseObject = is_object($_clause) ? $_clause : new $_clause();

        if (! $clauseObject instanceof ScenarioClauseInterface) {
            throw new Exception\ScenarioClauseException(sprintf(
                'Scenario clause object (%s) is must be an instance of %s',
                get_class($clauseObject),
                ScenarioClauseInterface::class
            ));
        }

        return new ScenarioClauseWrapper($clauseObject);
    }
}
