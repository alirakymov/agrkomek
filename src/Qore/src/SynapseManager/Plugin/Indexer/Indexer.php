<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Plugin\Indexer;

use ArrayObject;
use Qore\Collection\CollectionInterface;
use Qore\DealingManager\DealingManager;
use Qore\Manticore\ManticoreInterface;
use Qore\ORM\Entity\EntityInterface;
use Qore\Qore;
use Qore\SynapseManager\Artificer\ArtificerInterface;
use Qore\SynapseManager\Artificer\Service\ServiceArtificerInterface;
use Qore\SynapseManager\Plugin\PluginInterface;
use Qore\SynapseManager\Structure\Entity\SynapseService;
use Qore\SynapseManager\Structure\Entity\SynapseServiceSubject;
use Qore\SynapseManager\SynapseManager;

class Indexer implements PluginInterface
{
    /**
     * @var SynapseManager
     */
    private SynapseManager $sm;

    /**
     * @var ServiceArtificerInterface
     */ private ServiceArtificerInterface $artificer;

    /**
     * @var array
     */
    private $serviceNamespaces;

    /**
     * @var array
     */
    private $configs;

    /**
     * @var DealingManager
     */
    private $dm;

    /**
     * @var array
     */
    private $_configs;

    /**
     * @var SearchEngineInterface
     */
    private SearchEngineInterface $_engine;

    /**
     * @var ModelInterface
     */
    private ModelInterface $mapping;

    /**
     * Constructor
     *
     * @param \Qore\DealingManager\DealingManager $_dm
     * @param SearchEngineInterface $_searchEngine 
     * @param array $_serviceNamespaces
     * @param array $_configs
     */
    public function __construct(
        DealingManager $_dm, 
        SearchEngineInterface $_searchEngine, 
        array $_serviceNamespaces, 
        array $_configs
    ) {
        $this->dm = $_dm;
        $this->serviceNamespaces = $_serviceNamespaces;
        $this->configs = $_configs;
        $this->_engine = $_searchEngine;
    }

    /**
     * Set artificer instance
     *
     * @param \Qore\SynapseManager\Artificer\Service\ServiceArtificerInterface $_artificer
     *
     * @return void
     */
    public function setArtificer(ArtificerInterface $_artificer) : void
    {
        $this->artificer = $_artificer;
        $this->buildMapping();
        $this->_engine->setIndexName($this->getIndexName());
        $this->_engine->setMapping($this->mapping);
    }

    /**
     * Set synapse manager
     *
     * @param \Qore\SynapseManager\SynapseManager $_sm
     *
     * @return void
     */
    public function setSynapseManager(SynapseManager $_sm) : void
    {
        $this->sm = $_sm;
    }

    /**
     * Return engine instance
     *
     * @return SearchEngineInterface
     */
    public function getEngine(): SearchEngineInterface
    {
        return $this->_engine;
    }

    /**
     * Search in index
     *
     * @param \Qore\Collection\CollectionInterface $_filters 
     * @param array $_query (optional) 
     *
     * @return mixed 
     */
    public function search(CollectionInterface $_filters, array $_query = [])
    {
        $_filters = $this->prepareFilters($_filters);
        return $this->_engine->search($_filters, $_query);
    }

    /**
     * return index name
     *
     * @return string
     */
    public function getIndexName() : string
    {
        return preg_replace('/[^\d\w]+/', '_', sprintf(
            '%s.%s',
            mb_strtolower(basename(PROJECT_PATH)),
            mb_strtolower($this->artificer->getNameIdentifier())
        ));
    }

    /**
     * Generate mapping for synapse service
     *
     * @return void 
     */
    private function buildMapping(): void
    {
        $chain = $this->buildChain();

        $model = new Model();
        $model->isMapping(true);

        $dm = $this->dm;
        $dm(function($_builder) use ($chain) {
            foreach ($chain as $clause) {
                $_builder($clause);
            }
        })->launch($model);

         $this->mapping = $model(Model::MAPPING_STATE);
    }

    /**
     * Get mapping
     *
     * @return ModelInterface 
     */
    public function getMapping(): ModelInterface 
    {
        return $this->mapping;
    }


    /**
     * Prepare data for indexing
     *
     * @param \Qore\Collection\CollectionInterface $_objects
     *
     * @return filters
     */
    public function prepareFilters(CollectionInterface $_filters): array 
    {
        $chain = $this->buildChain();

        $model = new Model();
        $model->isSearch(true)->setFilters($_filters);

        $dm = $this->dm;
        $dm(function($_builder) use ($chain) {
            foreach ($chain as $clause) {
                $_builder($clause);
            }
        })->launch($model);

        /** @var CollectionInterface */
        $filters = $model['filters'];
        return $filters->toList();
    }

    /**
     * Prepare data for indexing
     *
     * @param \Qore\Collection\CollectionInterface $_objects
     *
     * @return void
     */
    public function prepareData(CollectionInterface $_objects): void
    {
        $chain = $this->buildChain();

        $model = new Model();
        $model->isIndexing(true)->setObjects($_objects);

        $dm = $this->dm;
        $dm(function($_builder) use ($chain) {
            foreach ($chain as $clause) {
                $_builder($clause);
            }
        })->launch($model);
    }

    /**
     * Generate chain for combine mapping structure
     *
     * @return array
     */
    public function buildChain() : array
    {
        $service = $this->artificer->getEntity();

        $chain = array_merge(
            [$this->artificer->getNameIdentifier() => $this->getHandlerForService($service)],
            $this->recursiveCombineChain($service, [$this->artificer->getNameIdentifier()]),
        );

        return $chain;
    }

    /**
     * Combine chain recursively
     *
     * @param \Qore\SynapseManager\Structure\Entity\SynapseService $service
     * @param array $_serviceNames
     *
     * @return array
     */
    private function recursiveCombineChain(SynapseService $_service, array $_serviceNames, array $_usedServices = []): array
    {
        if (! $subjects = $_service['subjectsFrom']) {
            return [];
        }

        $_usedServices[] = $_service['id'];

        $return = [];
        foreach ($subjects as $subject) {
            $artificer = $this->sm->getServicesRepository()->findByID($subject->iSynapseServiceTo);
            $artificerService = $artificer->getEntity();
            if (in_array($artificerService->id, $_usedServices)) {
                continue;
            }
            $serviceNames = array_merge($_serviceNames, [$subject->getReferenceName()]);
            $return[$path = implode('.', $serviceNames)] = $this->getHandlerForService($subject, $path);
            $return = array_merge($return, $this->recursiveCombineChain($artificer->getEntity(), $serviceNames, $_usedServices));
        }

        return $return;
    }

    /**
     * Initialize chain processor object for requested synapse service
     *
     * @param \Qore\SynapseManager\Structure\Entity\SynapseService|\Qore\SynapseManager\Structure\Entity\SynapseServiceSubject $_service
     * @param string $_path (optional)
     *
     * @return ChainProcessor
     */
    private function getHandlerForService($_service, string $_path = null) : ChainProcessor
    {
        $subject = null;
        if ($_service instanceof SynapseServiceSubject) {
            $subject = $_service;
            $_service = $_service->serviceTo();
        }

        $_path ??= '@root';

        $classname = $this->findClassname($_service) ?? $this->getDefaultHandlerClassname();
        return new ChainProcessor(
            new ExecuteHandler(
                new $classname(new DataTypeConverter($this->configs['mapping']['types'] ?? []), $subject)
            ),
            $_service,
            $_path
        );
    }

    /**
     * Find classname for _target of SynapseService
     *
     * @param \Qore\SynapseManager\Structure\Entity\SynapseService $_service
     *
     * @return string
     */
    private function findClassname(SynapseService $_service) : ?string
    {
        $classTemplate = '%s\\%s\\%s\\Plugin\\Indexer\\Handler';

        foreach ($this->serviceNamespaces as $namespace) {
            $class = sprintf(
                $classTemplate,
                $namespace,
                $_service->synapse->name,
                $_service->name,
            );

            if (class_exists($class) && in_array(HandlerInterface::class, class_implements($class))) {
                return $class;
            }
        }

        return null;
    }

    /**
     * Get default classname for handler
     *
     * @return string
     */
    private function getDefaultHandlerClassname() : string
    {
        return Handler::class;
    }

}
