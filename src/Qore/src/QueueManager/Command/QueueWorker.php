<?php

declare(strict_types=1);

namespace Qore\QueueManager\Command;

use Qore\Qore;
use Qore\QueueManager\QueueManager;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\VarExporter\VarExporter;

/**
 * Class: QueueInitWorkers
 *
 * @see SymfonyCommand
 */
class QueueWorker extends SymfonyCommand
{
    /**
     * defaultName
     *
     * @var string
     */
    protected static $defaultName = 'queue:worker';

    /**
     * config
     *
     * @var mixed
     */
    private $config = [];

    /**
     * queueManager
     *
     * @var QueueManager
     */
    private $queueManager = null;

    /**
     * setConfig
     *
     * @param array $_config
     */
    public function setConfig(array $_config)
    {
        $this->config = $_config;
    }

    /**
     * setQueueManager
     *
     * @param QueueManager $_qm
     */
    public function setQueueManager(QueueManager $_qm)
    {
        $this->queueManager = $_qm;
    }

    /**
     * configure
     *
     */
    protected function configure()
    {
        $this->setDescription('Исполнитель поручений от системы очередей (QueueManager)')
            ->addArgument('action', InputArgument::REQUIRED, 'Действие: start | stop | restart')
            ->addArgument('name', InputArgument::REQUIRED, 'Полное имя класса поручения');
    }

    /**
     * execute
     *
     * @param InputInterface $_input
     * @param OutputInterface $_output
     */
    protected function execute(InputInterface $_input, OutputInterface $_output)
    {
        $action = $_input->getArgument('action');
        if ($action == 'start') {
            return $this->start($_input, $_output);
        }
    }

    /**
     * start
     *
     * @param InputInterface $_input
     * @param OutputInterface $_output
     */
    protected function start(InputInterface $_input, OutputInterface $_output)
    {
        $jobClass= $_input->getArgument('name');
        $this->queueManager->subscribe($jobClass);
    }

    /**
     * getCommand
     *
     */
    protected function getCommand(string $_job)
    {
        $command = $_job::getWorkerCommand()
            ?? $this->config['worker-command']
                ?? "$('qore.paths.project-path')/assistant queue:worker start %s";
        return sprintf($command, $_job);
    }

    /**
     * getServices
     *
     */
    protected function getServices()
    {
        $mm = Qore::service('mm');
        return $mm('QSystem:Services')->all();
    }

}

