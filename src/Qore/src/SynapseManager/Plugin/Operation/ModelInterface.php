<?php

namespace Qore\SynapseManager\Plugin\Operation;

use Closure;
use Psr\Http\Message\ServerRequestInterface;
use Psr\SimpleCache\CacheInterface;
use Qore\DealingManager\ModelInterface as DealingManagerModelInterface;
use Qore\DealingManager\ScenarioInterface;
use Qore\Lock\Lock;

interface ModelInterface extends DealingManagerModelInterface
{
    /**
     * @var string storage entity index
     */
    const INDENTIFIER_INDEX = 'identifier';

    /**
     * @var string operation artificer name index
     */
    const ARTIFICER_NAME_INDEX = 'artificer-name';

    /**
     * @var string chain index
     */
    const CHAIN_INDEX = 'chain';

    /**
     * @var string action route index
     */
    const ACTION_ROUTE_INDEX = 'action-route';

    /**
     * @var string phase states container index
     */
    const PROCESSED_PHASES = 'processed-phases';

    /**
     * @var string phase states container index
     */
    const PHASE_STATE_CONTAINER = 'phase-states';

    /**
     * Set lock service
     *
     * @param \Qore\Lock\Lock $_lock
     *
     * @return ModelInterface
     */
    public function setLock(Lock $_lock): ModelInterface;

    /**
     * Set cache container
     *
     * @param CacheInterface $_cache
     *
     * @return CacheInterface
     */
    public function setCache(CacheInterface $_cache): ModelInterface;

    /**
     * Set Http request
     *
     * @param \Psr\Http\Message\ServerRequestInterface $_request
     *
     * @return ModelInterface
     */
    public function setRequest(ServerRequestInterface $_request) : ModelInterface;

    /**
     * Get Http request
     *
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    public function getRequest() : ServerRequestInterface;

    /**
     * Set task - used when chain launched from task processor
     *
     * @param TaskInterface $_task
     *
     * @return ModelInterface
     */
    public function setTask(TaskInterface $_task): ModelInterface;

    /**
     * Get task
     *
     * @return TaskInterface|null
     */
    public function getTask(): ?TaskInterface;

    /**
     * Set operation identifier
     *
     * @param string $_identifier
     *
     * @return ModelInterface
     */
    public function setIdentifier(string $_identifier): ModelInterface;

    /**
     * Get operation identifier
     *
     * @return string|null - operation UUIDv4 if saved
     */
    public function getIdentifier() : ?string;

    /**
     * Set artificer name
     *
     * @param string $_artificerName
     *
     * @return ModelInterface
     */
    public function setArtificerName(string $_artificerName): ModelInterface;

    /**
     * Get artificer name
     *
     * @return string
     */
    public function getArtificerName(): ?string;

    /**
     * Set chain array
     *
     * @param array $_chain
     *
     * @return ModelInterface
     */
    public function setChain(array $_chain): ModelInterface;

    /**
     * Get chain array
     *
     * @return array|null
     */
    public function getChain(): ?array;

    /**
     *
     * Set route name for any actions
     *
     * @param string $_actionRoute
     *
     * @return ModelInterface
     */
    public function setActionRoute(string $_actionRoute): ModelInterface;

    /**
     * Get action route name
     *
     * @return string|null
     */
    public function getActionRoute(): ?string;

    /**
     * Get/Create state container
     *
     * @return StateInterface
     */
    public function getState() : StateInterface;

    /**
     * set or check chain launched for initialize
     *
     * @param bool $_bool (optional)
     *
     * @return ModelInterface|bool
     */
    public function isInitialize(bool $_bool = null);

    /**
     * set or chekc chain launched for process
     *
     * @param bool $_bool (optional)
     *
     * @return ModelInterface|bool
     */
    public function isProcess(bool $_bool = null);

    /**
     * Set scenario processor of phases sequence
     *
     * @param ScenarioClauseInterface $_next
     *
     * @return ModelInterface
     */
    public function setNext(ScenarioInterface $_next): ModelInterface;

    /**
     * Fix handle phase and go to next node of chain
     *
     * @param PhaseInterface|null $_phase
     *
     * @return ResultInterface|null|void
     */
    public function next(PhaseInterface $_phase = null);

    /**
     * Check phase processing
     *
     * @param PhaseInterface $_phase
     *
     * @return bool
     */
    public function isProcessed(PhaseInterface $_phase): bool;

    /**
     * Synchronize cache data between processes
     *
     * @param Closure $_closure
     *
     * @return mixed
     */
    public function synchronized(Closure $_closure);

    /**
     * Sync model data with storage
     *
     * @param array $_data (optional) [TODO:description]
     *
     * @return ModelInterface
     */
    public function synchronize(array $_data = null): ModelInterface;

}
