<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Plugin\Operation;

use Psr\SimpleCache\CacheInterface;
use Qore\Config\ConfigContainerInterface;
use Qore\InterfaceGateway\Component\ComponentInterface;
use Qore\NotifyManager\NotifyManager;
use Qore\Qore;
use Qore\QueueManager\QueueManager; use Qore\SynapseManager\Artificer\Service\ServiceArtificerInterface;
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
class TaskProcess extends SymfonyCommand
{
    /**
     * defaultName
     *
     * @var string
     */
    protected static $defaultName = 'operation:task-process';

    /**
     * @var SynapseManager
     */
    protected SynapseManager $_sm;

    /**
     * @var QueueManager
     */
    protected QueueManager $_qm;

    /**
     * @var CacheInterface
     */
    protected CacheInterface $_cache;

    /**
     * @var ConfigContainerInterface
     */
    protected ConfigContainerInterface $_config;

    /**
     * @var NotifyManager
     */
    protected NotifyManager $_nm;

    /**
     * @param $name (optional)
     * @param \Qore\DealingManager\DealingManager $_dm
     * @param \Qore\QueueManager\QueueManager $_qm
     * @param \Psr\SimpleCache\CacheInterface $_cache
     * @param \Qore\Config\ConfigContainerInterface $_config
     */
    public function __construct(
        $name,
        SynapseManager $_sm,
        QueueManager $_qm,
        NotifyManager $_nm,
        CacheInterface $_cache,
        ConfigContainerInterface $_config
    ) {
        $this->_sm = $_sm;
        $this->_qm = $_qm;
        $this->_nm = $_nm;
        $this->_cache = $_cache;
        $this->_config = $_config;
        parent::__construct($name);
    }

    /**
     * Configure command
     *
     * @return
     */
    protected function configure()
    {
        $this->setDescription('Запуск отложенных задач инициированных в фазах плагина операции')
            ->addArgument(
                'identifier',
                InputArgument::REQUIRED,
                'Идентификатор задачи (генерируется плагином автоматически)'
            );
    }

    /**
     * execute
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            # - Get task from cache by identifier
            /** @var TaskInterface */
            $task = $this->_cache->get($input->getArgument('identifier'), null);
            if (is_null($task)) {
                return 0;
            }

            # - Get model from cache
            $model = $this->_cache->get($task->getOperationIdentifier(), null);
            if (is_null($model)) {
                return 0;
            }

            $model = new Model($model);
            $model->setTask($task);

            /** @var ServiceArtificerInterface */
            $artificer = ($this->_sm)($model->getArtificerName());
            if (is_null($artificer)) {
                return 0;
            }

            /** @var StorageInterface */
            $storage = $this->_sm->mm($task->getEntityClass())->where(function($_where) use ($task) {
                $_where(['@this.identifier' => $task->getOperationIdentifier()]);
            })->one();

            /** @var Operation */
            $operation = $artificer->plugin(Operation::class);
            $operation->setStorage($storage);

            $result = $operation->launch($model);

            if ($component = Qore::collection($result)->first()) {
                $component instanceof ComponentInterface &&
                    $this->_nm->send($component->compose(), new NotifyHub($storage->getIdentifier()));
            }

        } catch (\Throwable $t) {
            # - TODO: log it
            \dump($t);
        }

        return 0;
    }

}
