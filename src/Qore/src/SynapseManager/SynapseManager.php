<?php

declare(strict_types=1);

namespace Qore\SynapseManager;

use Qore\ORM;
use Qore\EventManager;
use Qore\DealingManager\DealingManager;
use Psr\Container\ContainerInterface;
use Qore\EventManager\EventManager as EventManagerEventManager;
use Qore\SynapseManager\Plugin\PluginInterface;
use Qore\SynapseManager\Plugin\PluginProvider;

/**
 * Class: SynapseFactory
 *
 */
class SynapseManager
{
    /**
     * config
     *
     * @var array
     */
    private $config = [];

    /**
     * mm
     *
     * @var Qore\ORM\ModelManager
     */
    private $mm = null;

    /**
     * em
     *
     * @var Qore\EventManager\EventManager
     */
    private ?EventManagerEventManager $em = null;

    /**
     * builder
     *
     * @var mixed
     */
    private $builder = null;

    /**
     * mapper
     *
     * @var Qore\ORM\Mapper\Mapper
     */
    private $mapper = null;

    /**
     * container
     *
     * @var Psr\Container\ContainerInterface
     */
    private $container = null;

    /**
     * artificersRepository
     *
     * @var mixed
     */
    private $artificersRepository = [];

    /**
     * @var PluginProvider
     */
    private $pluginProvider;

    /**
     * __construct
     *
     * @param mixed $_config
     * @param Qore\ORM\ModelManager $_mm
     * @param Structure\Builder $_builder
     * @param EventManager\EventManager $_em
     * @param ContainerInterface $_container
     */
    public function __construct(
        $_config,
        \Qore\ORM\ModelManager $_mm,
        Structure\Builder $_builder,
        EventManager\EventManager $_em,
        PluginProvider $_pluginProvider,
        ContainerInterface $_container
    ) {
        $this->setConfig($_config);
        $this->setModelManager($_mm);
        $this->setBuilder($_builder);
        $this->setEventManager($_em);
        $this->setContainer($_container);
        $this->setPluginProvider($_pluginProvider);
        $this->initialize();
    }

    /**
     * initialize
     *
     */
    public function initialize()
    {
        $mapperProvider = $this->mm->getMapperProvider();
        $mapperProvider->registerMapper(
            $this->mapper = new ORM\Mapper\Mapper(
                'SM',
                new ORM\Mapper\Driver\ArrayDriver($this->getBuilder()->getStructureForMapperDriver())
            )
        );
        $mapperProvider->initialize();
        $this->artificersRepository = [
            'service' => $this->container->get(Artificer\Service\Repository::class),
            'form' => $this->container->get(Artificer\Form\Repository::class),
        ];

        foreach ($this->artificersRepository as $repository) {
            $repository->init($this);
        }

        $this->pluginProvider->setSynapseManager($this);
    }

    /**
     * __invoke
     *
     * @param string $_subject
     */
    public function __invoke(string $_subject)
    {
        return strpos($_subject, '#') === false
            ? $this->artificersRepository['service']->findByName($_subject)
            : $this->artificersRepository['form']->findByName($_subject);
    }

    /**
     * getServiceRepository
     *
     */
    public function getServicesRepository() : Artificer\Service\Repository
    {
        return $this->artificersRepository['service'];
    }

    /**
     * getFormsRepository
     *
     */
    public function getFormsRepository() : Artificer\Form\Repository
    {
        return $this->artificersRepository['form'];
    }

    /**
     * getFieldRepository
     *
     */
    public function getFieldRepository() : Artificer\Field\Repository
    {
        return $this->fieldRepository;
    }

    /**
     * setConfig
     *
     * @param mixed $_config
     */
    public function setConfig($_config)
    {
        $this->config = $_config;
    }

    /**
     * setModelManager
     *
     * @param Qore\ORM\ModelManager $_mm
     */
    public function setModelManager(\Qore\ORM\ModelManager $_mm)
    {
        $this->mm = $_mm;
    }

    /**
     * getModelManager
     *
     */
    public function getModelManager() : \Qore\ORM\ModelManager
    {
        return $this->mm;
    }

    /**
     * getMapper
     *
     */
    public function getMapper() : ORM\Mapper\Mapper
    {
        return $this->mapper;
    }

    /**
     * config
     *
     * @param mixed $_param
     * @param mixed $_default
     */
    public function config($_param, $_default = null)
    {
        $config = &$this->config;
        $_param = explode('.', $_param);

        foreach ($_param as $paramKey) {
            if (isset($config[$paramKey])) {
                $config = &$config[$paramKey];
            } else {
                return $_default;
            }
        }

        return $config;
    }

    /**
     * setBuilder
     *
     * @param Structure\Builder $_builder
     */
    public function setBuilder(Structure\Builder $_builder)
    {
        $_builder->setSynapseManager($this);
        $this->builder = $_builder;
    }

    /**
     * getBuilder
     *
     */
    public function getBuilder() : Structure\Builder
    {
        return $this->builder;
    }

    /**
     * setEventManager
     *
     * @param EventManager\EventManager $_em
     */
    public function setEventManager(EventManager\EventManager $_em)
    {
        $this->em = $_em;
    }

    /**
     * Get event manager
     *
     * @return EventManager\EventManager
     */
    public function getEventManager() : EventManager\EventManager
    {
        return $this->em;
    }

    /**
     * Set plugin provider
     *
     * @param \Qore\SynapseManager\Plugin\PluginProvider $_pluginProvider
     *
     * @return void
     */
    public function setPluginProvider(PluginProvider $_pluginProvider) : void
    {
        $this->pluginProvider = $_pluginProvider;
    }

    /**
     * Create new instance of requested plugin
     *
     * @param string $_name
     *
     * @return PluginInterface
     */
    public function getPlugin(string $_name) : PluginInterface
    {
        return $this->pluginProvider->get($_name);
    }

    /**
     * setContainer
     *
     * @param ContainerInterface $_container
     */
    public function setContainer(ContainerInterface $_container)
    {
        $this->container = $_container;
    }

    /**
     * getContainer
     *
     */
    public function getContainer() : ContainerInterface
    {
        return $this->container;
    }

    /**
     * dm
     *
     * @param callable $_callback
     */
    public function dm(callable $_callback = null) : DealingManager
    {
        return $this->container->get(DealingManager::class)($_callback);
    }

    /**
     * mm
     *
     * @param mixed $_synapseName
     * @param mixed $_entity
     */
    public function mm($_synapseName = null, $_entity = null)
    {
        $mm = $this->mm;
        if (is_null($_synapseName) && is_null($_entity))  {
            return $mm;
        }

        return is_object($_synapseName) ? $mm($_synapseName) : $mm($_synapseName, $_entity);
    }

}
