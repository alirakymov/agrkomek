<?php

declare(strict_types=1);

namespace Qore\App\Services\Indexer;

use Qore\Manticore\Manticore;
use Qore\Manticore\ManticoreInterface;
use Qore\ORM\ModelManager;
use Qore\Qore;
use Qore\SynapseManager\SynapseManager;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class: {command}
 * Documentation: https://symfony.com/doc/current/console.html
 * @see Symfony\Component\Console\Command\Command
 */
class ArticleIndexer extends SymfonyCommand
{
    /**
     * defaultName
     *
     * @var string
     */
    protected static $defaultName = 'indexer:article';

    /**
     * configure
     *
     */
    protected function configure()
    {
        # - Command configure
        # - see https://symfony.com/doc/current/console.html
    }

    /**
     * execute
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sm = Qore::service(SynapseManager::class);
        $mm = Qore::service(ModelManager::class);

        $manticore = Qore::service(ManticoreInterface::class);

        dump($manticore);

        # - Command logic
        # - see https://symfony.com/doc/current/console.html
        return 0;
    }

}
