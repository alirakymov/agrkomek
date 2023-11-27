<?php

declare(strict_types=1);

namespace Qore\DealingManager;

use Psr\Container\ContainerInterface;

/**
 * Class: DealingManagerFactory
 *
 */
class DealingManagerFactory
{
    /**
     * __invoke
     *
     * @param ContainerInterface $_container
     */
    public function __invoke(ContainerInterface $_container)
    {
        return new DealingManager(
            new Scenario(new ScenarioClauseFactory()),
            new ScenarioBuilder()
        );
    }
}
