<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Plugin\Indexer;
use Qore\Qore;
use Qore\SynapseManager\SynapseManager;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * Class: {command}
 * Documentation: https://symfony.com/doc/current/console.html
 * @see Symfony\Component\Console\Command\Command
 */
class IndexerCommand extends SymfonyCommand
{
    private SynapseManager $sm;

    /**
     * defaultName
     *
     * @var string
     */
    protected static $defaultName = 'synapse:indexer';

    /**
     * @var array
     */
    protected $processes = [];

    /**
     * Configure command
     *
     * @return void 
     */
    protected function configure(): void
    {
        # - Command configure
        # - see https://symfony.com/doc/current/console.html
        $this->setDescription('Служба индексации данных синапс сервисов');
    }

    /**
     * execute
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int 
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->sm = Qore::service(SynapseManager::class);

        $servicesRepository = $this->sm->getServicesRepository();
        $indexingServices = $servicesRepository->filter(function($_service) {
            return (int)$_service->getEntity()->index === 1;
        })->compile();

        while (true) {
            foreach ($indexingServices as $service) {
                $process = $this->getServiceProcess($service);
                if (! $process->isRunning()) {
                    $this->processes[$service->getNameIdentifier()] = $process->restart();
                }
            }
            sleep(2);
        }

        return 0;
    }

    /**
     * Get service process
     *
     * @param  $_service
     *
     * @return \Symfony\Component\Process\Process
     */
    public function getServiceProcess($_service) : Process
    {
        $serviceName = $_service->getNameIdentifier();

        if (! isset($this->processes[$serviceName])) {
            $command = sprintf('%s/assistant synapse:indexer-process %s', $this->getCwd(), $serviceName);
            $this->processes[$serviceName] = Process::fromShellCommandline($command, null, null, null, 5000);
        }

        return $this->processes[$serviceName];
    }

    /**
     * Get project directory
     *
     * @return string
     */
    protected function getCwd(): string
    {
        return PROJECT_PATH;
    }

}
