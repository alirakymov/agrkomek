<?php

declare(strict_types=1);

namespace Qore\QueueManager\Command;

use Qore\Collection\CollectionInterface;
use Qore\Qore;
use Qore\QueueManager\QueueManager;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Documentation: https://symfony.com/doc/current/console.html
 * @see Symfony\Component\Console\Command\Command
 */
class QueueInitWorkers extends SymfonyCommand
{
    /**
     * defaultName
     *
     * @var string
     */
    protected static $defaultName = 'queue:inspect-workers';

    /**
     * config
     *
     * @var array 
     */
    private array $config = [];

    /**
     * queueManager
     *
     * @var QueueManager
     */
    private ?QueueManager $queueManager = null;

    /**
     * setConfig
     *
     * @param array $_config
     * @return void
     */
    public function setConfig(array $_config): void
    {
        $this->config = $_config;
    }

    /**
     * setQueueManager
     *
     * @param QueueManager $_qm
     * @return void
     */
    public function setQueueManager(QueueManager $_qm): void
    {
        $this->queueManager = $_qm;
    }

    /**
     * Configure
     *
     * @return void 
     */
    protected function configure(): void
    {
        $this->setDescription('Инспектирование исполнителей под зарегистрированные задачи');
    }

    /**
     * execute
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $io = new SymfonyStyle($input, $output);

        $jobs = $this->config['jobs'] ?? [];
        $services = $this->getServices();
        $mm = Qore::service('mm');

        $jobsInspectNeeded = false;
        foreach ($jobs as $job) {
            if (! class_exists($job)) {
                $jobsInspectNeeded = true;
                continue;
            }

            $queueName = $this->queueManager->getAdapter()->prepareQueueName($job);
            if (is_null($services->firstMatch(['name' => $queueName]))) {
                $service = $mm('QSystem:Services', [
                    'name' => $queueName,
                    'command' => $this->getCommand($job),
                    'autostart' => 1,
                    'numprocs' => $job::getWorkersNumber(),
                ]);
                $mm($service)->save();
            }
        }

        if ($jobsInspectNeeded) {
            # - Call queue:create-job inspect
        }
    }

    /**
     * Get shell command string
     *
     * @param string $_job
     * @return string
     */
    protected function getCommand(string $_job): string
    {
        $command = $_job::getWorkerCommand()
            ?? $this->config['worker-command']
                ?? "$('qore.paths.project-path')/assistant queue:worker start %s";
        return sprintf($command, addslashes($_job));
    }

    /**
     * Get services collection
     *
     * @return \Qore\Collection\CollectionInterface
     */
    protected function getServices(): CollectionInterface
    {
        $mm = Qore::service('mm');
        return $mm('QSystem:Services')->all();
    }

}

