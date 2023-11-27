<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Plugin\Chain;

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
     * @var SynapseService
     */
    private $_service;

    /**
     * @var SynapseServiceSubject
     */
    private ?SynapseServiceSubject $_subject;

    /**
     * @var string
     */
    private string $_path;

    /**
     * @var HandlerInterface
     */
    private HandlerInterface $_handler;

    /**
     * Constructor
     *
     * @param HandlerInterface $_handler
     * @param \Qore\SynapseManager\Structure\Entity\SynapseService $_service
     * @param \Qore\SynapseManager\Structure\Entity\SynapseServiceSubject $_subject (optional)
     * @param string $_path
     */
    public function __construct(HandlerInterface $_handler, SynapseService $_service, SynapseServiceSubject $_subject = null, string $_path)
    {
        $this->_service = $_service;
        $this->_subject = $_subject;
        $this->_path = $_path;
        $this->_handler = $_handler;
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
        return $this->wrapEnvironment($_model, function($_model) use ($_next) {
            return $this->_handler->handle($_model, $_next);
        });
    }

    /**
     * Initialize environment for this chain
     *
     * @param ModelInterface $_model
     * @param \Closure $_closure
     *
     * @return \Qore\DealingManager\ResultInterface
     */
    protected function wrapEnvironment(ModelInterface $_model, \Closure $_closure) : ResultInterface
    {
        # - Initialize subject
        $currentSubject = $_model->getSubject();
        $_model->setSubject($this->_subject);
        # - Initialize synapse service chain structure
        $serviceCollection = $_model->getServiceCollection();
        $_model->setServiceCollection($serviceCollection->appendItem($this->_service));
        # - Initialize handler path
        $currentPath = $_model->getPath();
        $_model->setPath($this->_path);
        # - Initialize chain cursor
        $currentCursor = $_model->getCursor();
        $_model->setCursor($this->findCurrentCursor($_model));
        # - Execute closure
        $result = $_closure($_model);
        # - Restore cursor
        $_model->setCursor($currentCursor);
        # - Restore path
        $_model->setPath($currentPath);
        # - Restore synapse service chain structure
        $_model->setServiceCollection($serviceCollection);
        # - Restore service subject
        $_model->setSubject($currentSubject);

        return $result;
    }

    /**
     * Find current cursor which relate with this path
     *
     * @return Model
     */
    protected function findCurrentCursor(ModelInterface $_model) : Model
    {
        $path = explode('.', $this->_path);
        array_shift($path);
        $state = ($_model)(Model::CHAIN_STATE);

        if (! $path) {
            return $state;
        }

        foreach ($path as $point) {
            $state = $state($point);
        }

        return $state;
    }

}
