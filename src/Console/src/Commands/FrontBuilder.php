<?php

declare(strict_types=1);

namespace Qore\Console\Commands;

use Qore\Qore;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * Class: {command}
 * Documentation: https://symfony.com/doc/current/console.html
 * @see Symfony\Component\Console\Command\Command
 */
class FrontBuilder extends SymfonyCommand
{
    /**
     * defaultName
     *
     * @var string
     */
    protected static $defaultName = 'front:build';

    /**
     * configure
     *
     */
    protected function configure()
    {
        $this->addOption('dev', null, InputOption::VALUE_NONE, 'Build in development mode', null)
            ->setDescription('Front application builder command');
    }

    /**
     * execute
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $buildType = $input->getOption('dev') ? 'dev' : 'prod';
        $frontappPath = Qore::config('qore.paths.frontapp');

        $process = Process::fromShellCommandline(
            sprintf('yarn --cwd %s run %s', $frontappPath, $buildType),
            null,
            null,
            null,
            0
        );
        $process->setTty(true);
        $process->start();
        $process->wait();
    }

}
