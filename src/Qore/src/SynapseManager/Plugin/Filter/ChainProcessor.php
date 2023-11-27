<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Plugin\Filter;

use Iterator;
use Qore\Collection\CollectionInterface;
use Qore\DealingManager\Result;
use Qore\DealingManager\ResultInterface;
use Qore\DealingManager\ScenarioClauseInterface;
use Qore\DealingManager\ScenarioInterface;
use Qore\SynapseManager\Structure\Entity\SynapseService;
use Qore\SynapseManager\Structure\Entity\SynapseServiceSubject;

class ChainProcessor implements ScenarioClauseInterface
{
    private $model;

    /**
     * @var ExecutableInterface
     */
    private $executeObject;

    /**
     * @var SynapseService
     */
    private $service;

    /**
     * @var string
     */
    private string $path;

    /**
     * Constuctor
     *
     * @param ExecutableInterface $_executeObject
     * @param \Qore\SynapseManager\Structure\Entity\SynapseService $_service
     */
    public function __construct(ExecutableInterface $_executeObject, SynapseService $_service, string $_path)
    {
        $this->executeObject = $_executeObject;
        $this->service = $_service;
        $this->path = $_path;
    }

    /**
     * Process building mapping
     *
     * @param $_model
     * @param \Qore\DealingManager\ScenarioInterface $_next
     *
     * @return \Qore\DealingManager\ResultInterface
     */
    public function processClause($_model, ScenarioInterface $_next) : ResultInterface
    {
        $this->model = $_model;

        return $this->wrapEnvironment(function($_model) use ($_next) {
            $result = $this->executeObject->execute($_model);
            $result && $_next->process($_model);
            return new Result();
        });
    }

    /**
     * Initialize environment for this chain
     *
     * @param \Closure $_closure
     *
     * @return \Qore\DealingManager\ResultInterface
     */
    protected function wrapEnvironment(\Closure $_closure) : ResultInterface
    {
        # - Initialize synapse service chain structure
        $serviceCollection = $this->model->getServiceCollection();
        $this->model->setServiceCollection($serviceCollection->appendItem($this->service));
        # - Initialize mapping cursor
        $this->model[Model::STATE_CURSOR] = $this->findCurrentCursor();
        $currentPath = $this->model['path'] ?? null;
        $this->model['path'] = $this->path;
        # - Execute closure
        $result = $_closure($this->model);
        # - Restore path
        $this->model['path'] = $currentPath;
        # - Restore synapse service chain structure
        $this->model->setServiceCollection($serviceCollection);

        return $result;
    }

    /**
     * Find current cursor which relate with this path
     *
     * @return Model
     */
    protected function findCurrentCursor() : Model
    {
        $path = explode('.', $this->path);
        array_shift($path);
        $state = ($this->model)(Model::STATE_CURSOR);

        if (! $path) {
            return $state;
        }

        $state = $state(Model::STATE_CURSOR);
        while ($point = current($path)) {
            $state = $state($point);
            if (next($path)) {
                $state = $state('properties');
            }
        }

        return $state;
    }

    /**
     * Set executable object
     *
     * @param ExecutableInterface $_object
     *
     * @return void
     */
    protected function setExecuteObject(ExecutableInterface $_object) : void
    {
        $this->executeObject = $_object;
    }

}
