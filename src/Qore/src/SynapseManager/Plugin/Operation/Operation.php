<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Plugin\Operation;

use Closure;
use Opis\Closure\SerializableClosure;
use Psr\SimpleCache\CacheInterface;
use Qore\Config\ConfigContainer;
use Qore\Config\ConfigContainerInterface;
use Qore\DealingManager\DealingManager;
use Qore\DealingManager\ResultInterface;
use Qore\Lock\Lock;
use Qore\NotifyManager\NotifyManager;
use Qore\NotifyManager\SubscriberInterface;
use Qore\Qore;
use Qore\QueueManager\QueueManager;
use Qore\SynapseManager\Artificer\ArtificerInterface;
use Qore\SynapseManager\Artificer\Service\ServiceArtificerInterface;
use Qore\SynapseManager\Plugin\PluginInterface;
use Qore\SynapseManager\SynapseManager;

class Operation implements OperationInterface, PluginInterface
{
    /**
     * @var SynapseManager
     */
    private $_sm;

    /**
     * @var ServiceArtificerInterface
     */
    private ArtificerInterface $_artificer;

    /**
     * @var array
     */
    protected array $_chain;

    /**
     * @var StorageInterface
     */
    protected StorageInterface $_storage;

    /**
     * @var DealingManager
     */
    protected DealingManager $_dm;

    /**
     * @var Lock
     */
    protected Lock $_lock;

    /**
     * @var CacheInterface
     */
    protected CacheInterface $_cache;

    /**
     * @var ConfigContainerInterface
     */
    protected ConfigContainerInterface $_config;

    /**
     * @var array<TaskInterface> - array of deferred tasks
     */
    protected array $deferredTasks = [];

    /**
     * @var ModelInterface
     */
    protected ModelInterface $model;

    /**
     * @var QueueManager
     */
    protected QueueManager $_qm;

    /**
     * @var string
     */
    protected ?string $_actionRoute = null;

    /**
     * @var NotifyManager
     */
    protected NotifyManager $_nm;

    /**
     * Constructor
     *
     * @param \Qore\DealingManager\DealingManager $_dm
     */
    public function __construct(
        DealingManager $_dm,
        QueueManager $_qm,
        NotifyManager $_nm,
        Lock $_lock,
        CacheInterface $_cache,
        ConfigContainerInterface $_config
    ) {
        $this->_dm = $_dm;
        $this->_qm = $_qm;
        $this->_nm = $_nm;
        $this->_lock = $_lock;
        $this->_cache = $_cache;
        $this->_config = $_config;
    }

    /**
     * Return cache container
     *
     * @return \Psr\SimpleCache\CacheInterface
     */
    public function getCache(): CacheInterface
    {
        return $this->_cache;
    }

    /**
     * @inheritdoc
     */
    public function getConfig(): ConfigContainer
    {
        return $this->_config;
    }

    /**
     * Set SynapseManager instance
     *
     * @param \Qore\SynapseManager\SynapseManager $_sm
     *
     * @return void
     */
    public function setSynapseManager(SynapseManager $_sm) : void
    {
        $this->_sm = $_sm;
    }

    /**
     * Set Artificer instance
     *
     * @param \Qore\SynapseManager\Artificer\Service\ServiceArtificerInterface $_artificer
     *
     * @return void
     */
    public function setArtificer(ArtificerInterface $_artificer) : void
    {
        $this->_artificer = $_artificer;
    }

    /**
     * @inheritdoc
     */
    public function setChain(array $_chain): OperationInterface
    {
        $this->_chain = $_chain;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setStorage(StorageInterface $_storage): OperationInterface
    {
        $this->_storage = $_storage;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setActionRoute(string $_actionRoute): OperationInterface
    {
        $this->_actionRoute = $_actionRoute;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function subscribe(SubscriberInterface $_subscriber): OperationInterface
    {
        $this->_nm->subscribe(
            $_subscriber,
            [new NotifyHub($this->_storage->getIdentifier())]
        );

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function launch(ModelInterface $_model = null) : ResultInterface
    {
        if (is_null($_model)) {
            $_model = new Model($this->_storage->getData());
            $requestModel = $this->_artificer->getModel();
            $request = ! is_null($requestModel) ? $requestModel->getRequest() : null;
            ! is_null($request) && $_model->setRequest($request);
        }

        $this->model = $_model;

        # - Initialize model dependencies
        $_model->setLock($this->_lock);
        $_model->setCache($this->_cache);
        # - Set operation identifier
        if (is_null($_model->getIdentifier())) {
            $_model->setIdentifier($this->_storage->getIdentifier());
        }

        # - Sync model
        $_model->synchronize((array)$this->_storage->getData());

        # - Set artificer name
        if (is_null($_model->getArtificerName())) {
            $_model->setArtificerName($this->_artificer->getNameIdentifier());
        }

        # - Save chain to model
        if (is_null($_model->getChain())) {
            $_model->setChain($this->_chain);
        }

        # - Set action uri
        if (! is_null($this->_actionRoute)) {
            $_model->setActionRoute($this->_actionRoute);
        }

        # - Build chain
        $chain = ($this->_dm)(function($_builder) use ($_model) {
            foreach($_model->getChain() as $index => $phase) {
                $_builder($this->getPhaseProcessor($phase, $index));
            }
        });

        # - Initialize action of chain phases
        $_model->isInitialize(true);
        (clone $chain)->launch($_model);
        # - Process action of chain phases
        $_model->isProcess(true);
        $result = $chain->launch($_model);

        # - Save storage entity
        $this->_storage->setData((array)$_model);
        $this->_sm->mm($this->_storage)->save();

        if ($this->deferredTasks) {
            # - Set model to cache if it's absent in
            if (! $this->_cache->has($_model->getIdentifier())) {
                $this->_cache->set($_model->getIdentifier(), (array)$_model);
            }
            # - Send each deferred task to task launching queue
            $identifiers = [];
            foreach ($this->deferredTasks as $task) {
                $this->_cache->set($task->getIdentifier(), $task);
                $identifiers[] = $task->getIdentifier();
            }

            $this->_qm->publish(new TaskQueue([
                'identifier' => $identifiers,
            ]));
        }

        return $result;
    }

    /**
     * Create chain processor instance for phase
     *
     * @param $_phase
     * @param string|int $_index
     *
     * @return PhaseProcessor
     */
    protected function getPhaseProcessor($_phase, $_index) : PhaseProcessor
    {
        $_phase = is_string($_phase) ? new $_phase($_index) : $_phase;

        if (! is_null($task = $this->model->getTask())
            && $task->getPhaseIdentifier() === $_phase->getIdentifier()) {
            $_phase = $this->getTaskPhase($task, $_index);
        }

        return new PhaseProcessor($_phase, $this);
    }

    /**
     * Create phase for deferred task
     *
     * @param TaskInterface $_task
     * @param $_index
     *
     * @return PhaseInterface
     */
    protected function getTaskPhase(TaskInterface $_task, $_index): PhaseInterface
    {
        /** @var PhaseInterface */
        $phase = null;
        $code = <<<END
            use Qore\SynapseManager\Plugin\Operation\ModelInterface;
            use Qore\DealingManager\ResultInterface;
            \$phase = new class('{$_index}', '{$_task->getPhaseClass()}') extends \\%s {
                public function process(ModelInterface \$_model): ResultInterface
                {
                    \$closure = \$_model->getTask()->getClosure();
                    return \$closure(\$this) ?? \$this->result();
                }
            };
        END;

        # - Run code for generate intance of phase
        eval(sprintf($code, $_task->getPhaseClass()));
        return $phase;
    }

    /**
     * @inheritdoc
     */
    public function defer(Closure $_closure, string $_phaseIdentifier, string $_phaseClass, ?string $_suffix): TaskInterface
    {
        $cache = $this->getCache();
        $config = $this->getConfig();
        # - Calculate closure hash
        $closureHash = $this->getClosureHash($_closure);
        # - In production mode save to cache storage serialized _closure
        if (! $config('debug', true)) {
            # - Save to cache if it hasn't
            $cache(static::TASK_CLOSURES_INDEX, function ($_closures) use (&$_closure, $closureHash) {
                $_closures ??= [];
                $_closure = ($_closures[$closureHash] ??= serialize(new SerializableClosure($_closure)));
                return $_closures;
            });
        }

        $_suffix = (string)$_suffix . $closureHash;
        return $this->registerTask($_closure, $_phaseIdentifier, $_phaseClass, $_suffix);
    }

    /**
     * Generate unique hash for closure
     *
     * @param Closure $_closure
     *
     * @return string
     */
    protected function getClosureHash(Closure $_closure): string
    {
        $reflection = new \ReflectionFunction($_closure);
        return sha1($reflection->getFileName() . $reflection->getStartLine() . $reflection->getEndLine());
    }

    /**
     * Register deferred task
     *
     * @param string/Closure $_closure
     * @param string $_phaseIdentifier
     * @param string $_phaseClass
     * @param string $_suffix
     *
     * @return TaskInterface
     */
    protected function registerTask($_closure, string $_phaseIdentifier, string $_phaseClass, string $_suffix): TaskInterface
    {
        # - Calculate task identifier as hash
        $hash = sha1($this->model->getIdentifier() . $_suffix);
        # - Create task
        $task = new Task(
            $hash,
            $this->model->getIdentifier(),
            $_phaseIdentifier,
            $_phaseClass,
            $this->_storage->getEntityName(),
            $_closure
        );
        # - Save task to array
        $this->deferredTasks[] = $task;

        return $task;
    }

}
