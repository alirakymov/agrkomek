<?php

declare(strict_types=1);

namespace Qore\QueueManager;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Interface: WorkerInterface
 *
 */
interface WorkerInterface
{
    /**
     * execute
     *
     * @param InputInterface $_input
     * @param OutputInterface $_output
     */
    public function execute(InputInterface $_input, OutputInterface $_output);

}
