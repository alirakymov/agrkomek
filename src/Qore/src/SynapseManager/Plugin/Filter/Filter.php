<?php

namespace Qore\SynapseManager\Plugin\Filter;

use Mezzio\Router\RouteResult;
use Qore\Collection\CollectionInterface;
use Qore\DealingManager\DealingManager;
use Qore\Form\FormManagerInterface;
use Qore\SynapseManager\Artificer\ArtificerInterface;
use Qore\SynapseManager\Plugin\PluginInterface;
use Qore\SynapseManager\Structure\Entity\SynapseService;
use Qore\SynapseManager\Structure\Entity\SynapseServiceSubject;
use Qore\SynapseManager\SynapseManager;
use Psr\Http\Message\ServerRequestInterface;
use Qore\Form\Field\Submit;
use Qore\Qore;

class Filter implements FilterInterface, PluginInterface
{
    /**
     * @var SynapseManager
     */
    private SynapseManager $_sm;

    /**
     * @var ArtificerInterface
     */
    private ArtificerInterface $_artificer;

    /**
     * @var FormManagerInterface
     */
    private FormManagerInterface $_fm;

    /**
     * @var DealingManager
     */
    private DealingManager $_dm;

    /**
     * @var array
     */
    private array $namespaces;

    /**
     * Constructor
     *
     * @param \Qore\DealingManager\DealingManager $_dm 
     * @param \Qore\Form\FormManagerInterface $_fm 
     */
    public function __construct(DealingManager $_dm, FormManagerInterface $_fm, array $_namespaces)
    {
        $this->_dm = $_dm;
        $this->_fm = $_fm;
        $this->namespaces = $_namespaces;
    }

    /**
     * Set synapse manager
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
     * Set Artificer
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
     * Generaten new form instance
     *
     * @param \Psr\Http\Message\RequestInterface $_request
     * @param \Qore\Collection\CollectionInterface $_filters
     *
     * @return \Qore\Form\FormManagerInterface
     */
    public function getForm(ServerRequestInterface $_request, CollectionInterface $_filters): FormManagerInterface
    {
        $chain = $this->buildChain();

        $model = new Model();
        $model->isBuild(true)
            ->setRequest($_request)
            ->setForm($this->generateForm($_request))
            ->setSynapseManager($this->_sm)
            ->setFilters($_filters);

        $dm = $this->_dm;
        $dm(function($_builder) use ($chain) {
            foreach ($chain as $clause) {
                $_builder($clause);
            }
        })->launch($model);

        $this->completeForm($fm = $model->getForm(), $_request);

        return $fm;
    }

    /**
     * Generate new form
     *
     * @return \Qore\Form\FormManagerInterface
     */
    protected function generateForm(): FormManagerInterface
    {
        $fm = clone $this->_fm;
        $fm->setName(sprintf('%s-filter-form', $this->_artificer->getNameIdentifier()));
        $fm->setMethod('GET');
        return $fm;
    }

    /**
     * Complete form
     *
     * @param \Qore\Form\FormManagerInterface $_fm
     * @param \Psr\Http\Message\ServerRequestInterface $_request
     *
     * @return void
     */
    protected function completeForm(FormManagerInterface $_fm, ServerRequestInterface $_request): void
    {
        # - Clean query params
        $queryParams = $_request->getQueryParams();
        foreach ($_fm->getFields() as $field) {
            unset($queryParams[$field->getName()]);
        }

        # - Set form action url
        /** @var RouteResult */
        $routeResult = $_request->getAttribute(RouteResult::class);
        $_fm->setAction(Qore::url(
            $routeResult->getMatchedRouteName(), 
            $routeResult->getMatchedParams(),
            $queryParams
        ));

        # - Set submit button
        $_fm->setField(new Submit('submit', [
            'label' => 'Искать'
        ]));
    }

    /**
     * Generate chain for combine mapping structure
     *
     * @return array
     */
    public function buildChain() : array
    {
        $service = $this->_artificer->getEntity();

        $chain = array_merge(
            [$this->_artificer->getNameIdentifier() => $this->getHandlerForService($service)],
            $this->recursiveCombineChain($service, ['@root']),
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
    private function recursiveCombineChain(SynapseService $_service, array $_serviceNames) : array
    {
        if (! $subjects = $_service['subjectsFrom']) {
            return [];
        }

        $return = [];
        foreach ($subjects as $subject) {
            $artificer = $this->_sm->getServicesRepository()->findByID($subject->iSynapseServiceTo);
            $serviceNames = array_merge($_serviceNames, [$subject->getReferenceName()]);
            $return[$path = implode('.', $serviceNames)] = $this->getHandlerForService($subject, $path);
            $return = array_merge($return, $this->recursiveCombineChain($artificer->getEntity(), $serviceNames));
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
                new $classname($subject)
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
        $classTemplate = '%s\\%s\\%s\\Plugin\\Filter\\Handler';

        foreach ($this->namespaces as $namespace) {
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
