<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Plugin\Indexer;

use DateTime;
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
class IndexerProcess extends SymfonyCommand
{
    /**
     * defaultName
     *
     * @var string
     */
    protected static $defaultName = 'synapse:indexer-process';

    /**
     * @var SynapseManager
     */
    private SynapseManager $sm;

    /**
     * configure
     *
     */
    protected function configure(): void 
    {
        $this->setDescription('Процесс индексации данных заданного синапс сервиса')
            ->addArgument(
                'synapse-service',
                InputArgument::REQUIRED,
                'Целевой сервис синапса, код которого необходимо сгенерировать'
            );
    }

    /**
     * execute
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $sm = Qore::service(SynapseManager::class);
        $mm = Qore::service(ModelManager::class);
        # - Get connection instance
        $dbConnection = $mm->getAdapter()->getDriver()->getConnection();

        $artificer = $sm($input->getArgument('synapse-service'));
        $serviceEntity = $artificer->getEntity();

        $indexerEntity = $mm('QSynapse:SynapsePluginIndexer')
            ->where(function ($_where) use ($serviceEntity) {
                $_where(['@this.iSynapseService' => $serviceEntity['id']]);
            })->one() ?? $mm('QSynapse:SynapsePluginIndexer', [
                'iSynapseService' => $serviceEntity['id'],
            ]);

        /** @var Indexer */
        $indexer = $artificer->plugin(Indexer::class);
        $mapping = $indexer->getMapping();
        $engine = $indexer->getEngine();
        $engine->setMapping($mapping);
        # - check index mapping
        if ($indexerEntity->isNew() || sha1(json_encode($indexerEntity->mappingState)) !== sha1(json_encode($mapping)) ) {
            $engine->make();
            $indexerEntity->resetIndexDate();
            $indexerEntity->mappingState = $mapping;
            $mm($indexerEntity)->save();
        }

        $gw = $artificer->getLocalGateway();

        $i = 0;
        while (true) {

            $dbConnection->connect();

            $objects = $artificer->mm()->select(function($_select) use ($i) {
                $_select->limit($c = 50)->offset($i*$c - ($i ? 1 : 0))->order('@this.__updated');
            })->where(function($_where) use ($indexerEntity) {
                $_where(['@this.__indexed' => 0]);
                // $_where->greaterThan('@this.__updated', $indexerEntity->lastIndexDate->format('Y-m-d H:i:s'));
            })->all();

            if (! $objects->count()) {
                break;
            }

            $objects = (clone $gw)->where(function ($_where) use ($objects) {
                $_where(['@this.id' => $objects->extract('id')->toList()]);
            })->all();

            try {
                $indexer->prepareData($objects);
                $result = $engine->index($objects->map(
                    fn($_obj) => $_obj->toArray(true)
                )->toList());

            } catch(\Throwable $e) {
                # [TODO] Log it
            }

            $r = memory_get_usage();
            $mm->getEntityProvider()->reset();

            $lastObject = $objects->last();

            if ($i++ > 100) {
                $lastObject = $objects->last();
                break;
            }

            $dbConnection->disconnect();
            usleep(250000);
        }

        if (isset($lastObject)) {
            $indexerEntity->lastIndexDate = (new DateTime())->setTimestamp($lastObject['__updated']);
            $dbConnection->connect();
            $mm($indexerEntity)->save();
            $dbConnection->disconnect();
        }

        sleep(5);
        # - Command logic
        # - see https://symfony.com/doc/current/console.html
        return 0;
    }

}
