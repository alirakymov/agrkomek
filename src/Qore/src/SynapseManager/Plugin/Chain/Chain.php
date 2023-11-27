<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Plugin\Chain;

use Qore\SynapseManager\Artificer\ArtificerInterface;
use Qore\SynapseManager\Plugin\PluginInterface;
use Qore\SynapseManager\Structure\Entity\SynapseService;
use Qore\SynapseManager\Structure\Entity\SynapseServiceSubject;
use Qore\SynapseManager\SynapseManager;

class Chain implements PluginInterface
{
    const ROOT_SERVICE_POINTER = '@root';

    /**
     * @var SynapseManager
     */
    private $_sm;
    /**
     * @var ArtificerInterface
     */
    private $_artificer;

    /**
     * @var string
     */
    private string $_defaultHandlerClass;

    /**
     * @var string
     */
    private string $_handlerPattern;

    /**
     * @var array
     */
    private array $_namespaces;

    /**
     * Constructor
     *
     */
    public function __construct(array $_namespaces)
    {
        $this->_namespaces = $_namespaces;
    }

    /**
     * Set SynapseManager instance
     *
     * @param \Qore\SynapseManager\SynapseManager $_sm
     *
     * @return void
     */
    public function setSynapseManager(SynapseManager $_sm): void
    {
        $this->_sm = $_sm;
    }

    /**
     * Set Artificer instance
     *
     * @param \Qore\SynapseManager\Artificer\ArtificerInterface $_artificer
     *
     * @return void
     */
    public function setArtificer(ArtificerInterface $_artificer): void
    {
        $this->_artificer = $_artificer;
    }

    /**
     * Build chain
     *
     * @param string $_defaultHandlerClass
     * @param string $_handlerPattern
     * @param bool $_recursive (optional)
     *
     * @return array
     */
    public function build(string $_defaultHandlerClass, string $_handlerPattern, bool $_recursive = true) : array
    {
        $this->_defaultHandlerClass = $_defaultHandlerClass;
        $this->_handlerPattern = $_handlerPattern;

        $service = $this->_artificer->getEntity();

        $chain = array_merge(
            [static::ROOT_SERVICE_POINTER => $this->getProcessorForService($service)],
            $this->combineChain($service, [static::ROOT_SERVICE_POINTER], $_recursive),
        );

        return $chain;
    }

    /**
     * Combine chain recursively
     *
     * @param \Qore\SynapseManager\Structure\Entity\SynapseService $service
     * @param array $_serviceNames
     * @param bool $_recursive (optional)
     *
     * @return array
     */
    private function combineChain(SynapseService $_service, array $_serviceNames, bool $_recursive = true) : array
    {
        if (! $subjects = $_service['subjectsFrom']) {
            return [];
        }

        $return = [];
        foreach ($subjects as $subject) {
            $_artificer = $this->_sm->getServicesRepository()->findByID($subject->iSynapseServiceTo);
            $serviceNames = array_merge($_serviceNames, [$subject->getReferenceName()]);
            $return[$path = implode('.', $serviceNames)] = $this->getProcessorForService($subject, $path);
            $_recursive && $return = array_merge(
                $return,
                $this->combineChain($_artificer->getEntity(), $serviceNames)
            );
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
    private function getProcessorForService($_service, string $_path = null) : ChainProcessor
    {
        $subject = null;
        if ($_service instanceof SynapseServiceSubject) {
            $subject = $_service;
            $_service = $_service->serviceTo();
        }

        $_path ??= static::ROOT_SERVICE_POINTER;
        $classname = $this->findHandlerClass($_service);

        return new ChainProcessor(
            new $classname($this->_sm),
            $_service,
            $subject,
            $_path
        );
    }

    /**
     * Find dispatcher class for target of SynapseService
     *
     * @param \Qore\SynapseManager\Structure\Entity\SynapseService $_service
     *
     * @return string
     */
    private function findHandlerClass(SynapseService $_service) : ?string
    {
        $classTemplate = '%s\\%s\\%s\\%s';

        foreach ($this->_namespaces as $namespace) {
            $class = sprintf(
                $classTemplate,
                $namespace,
                $_service->synapse->name,
                $_service->name,
                $this->_handlerPattern,
            );

            if (class_exists($class) && in_array(HandlerInterface::class, class_implements($class))) {
                return $class;
            }
        }

        return $this->_defaultHandlerClass;
    }

}
