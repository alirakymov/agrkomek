<?php

namespace Qore\SynapseManager\Plugin\Operation;

use Closure;
use Psr\SimpleCache\CacheInterface;
use Qore\Config\ConfigContainer;
use Qore\DealingManager\ResultInterface;
use Qore\NotifyManager\SubscriberInterface;

interface OperationInterface
{
    /**
     * @var string - cache index for task processes
     */
    const RUNNING_TASKS = 'running-tasks';

    /**
     * @var string - index for task closures collection on cache storage
     */
    const TASK_CLOSURES_INDEX = 'task-closures';

    /**
     * Get cache container interface
     *
     * @return \Psr\SimpleCache\CacheInterface
     */
    public function getCache(): CacheInterface;

    /**
     * Get config container
     *
     * @return \Qore\Config\ConfigContainer
     */
    public function getConfig(): ConfigContainer;

    /**
     * Set chain as array of phase classes
     *
     * @param array $_chain
     *
     * @return OperationInterface
     */
    public function setChain(array $_chain): OperationInterface;

    /**
     * Set storage entity
     *
     * @param StorageInterface $_storage
     *
     * @return OperationInterface
     */
    public function setStorage(StorageInterface $_storage): OperationInterface;

    /**
     * Set nome of route for any actions
     *
     * @param string $_actionRoute
     *
     * @return OperationInterface
     */
    public function setActionRoute(string $_actionRoute): OperationInterface;

    /**
     * Subscribe to operation hub
     *
     * @param \Qore\NotifyManager\SubscriberInterface $_subscriber
     *
     * @return OperationInterface
     */
    public function subscribe(SubscriberInterface $_subscriber): OperationInterface;

    /**
     * Launch chain of phases
     *
     * @param ModelInterface|null $_model (optional)
     *
     * @return \Qore\DealingManager\ResultInterface
     */
    public function launch(ModelInterface $_model = null) : ResultInterface;

    /**
     * Register closure and register deferred task
     *
     * @param \Closure $_closure
     * @param string $_phaseIdentifier
     * @param string $_phaseClass
     * @param string|null $_suffix (optional)
     *
     * @return TaskInterface
     */
    public function defer(Closure $_closure, string $_phaseIdentifier, string $_phaseClass, ?string $_suffix): TaskInterface;

}
