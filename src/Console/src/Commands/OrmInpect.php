<?php

declare(strict_types=1);

namespace Qore\Console\Commands;


use Qore\Qore;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class: {command}
 * Documentation: https://symfony.com/doc/current/console.html
 * @see Symfony\Component\Console\Command\Command
 */
class OrmInpect extends SymfonyCommand
{
    /**
     * defaultName
     *
     * @var string
     */
    protected static $defaultName = 'orm:inspect';

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
        # - Command logic
        # - see https://symfony.com/doc/current/console.html
        $results = [];

        $engineer = new \Qore\ORM\Mapper\Engineer(
            Qore::service(\Qore\Database\Adapter\Adapter::class)
        );

        $systemMapper = new \Qore\ORM\Mapper\Mapper(
            'QSystem',
            new \Qore\ORM\Mapper\Driver\ArrayDriver(Qore::config('orm.QSystem'))
        );
        $systemMapper->setModelManager(Qore::service(\Qore\ORM\ModelManager::class));
        $result = $engineer->inspect($systemMapper);
        $results[] = $result->getModifiedModels();
        $result->applyChanges();

        $synapseMapper = new \Qore\ORM\Mapper\Mapper(
            'QSynapse',
            new \Qore\ORM\Mapper\Driver\ArrayDriver(Qore::config('orm.QSynapse'))
        );
        $synapseMapper->setModelManager(Qore::service(\Qore\ORM\ModelManager::class));
        $result = $engineer->inspect($synapseMapper);
        $results[] = $result->getModifiedModels();
        $result->applyChanges();

        $sm = Qore::service(\Qore\SynapseManager\SynapseManager::class);
        $result = $engineer->inspect($sm->getMapper());
        $results[] = $result->getModifiedModels();
        $result->applyChanges();

        foreach ($results as $result) {
            var_dump($result);
        }

        return 0;
    }
}
