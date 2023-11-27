<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Artificer\Service;

use Qore\Collection\Collection;
use Qore\DealingManager;
use Qore\SynapseManager\Artificer;
use Qore\SynapseManager\Structure\Entity;
use Qore\Router\RouteCollector;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Qore\ORM\Mapper\Table\Column\Datetime;
use Qore\ORM\Sql\Where;
use Qore\SynapseManager\Artificer\RequestModel;
use Qore\SynapseManager\Artificer\Service\Filter\TypeInterface;

/**
 * Class: ServiceArtificer
 *
 * @see ServiceArtificerInterface
 */
class ServiceArtificer extends Artificer\ArtificerBase implements ServiceArtificerInterface
{
    /**
     * entity
     *
     * @var mixed
     */
    protected $entity = null;

    /**
     * @var ?RequestModel
     */
    protected ?RequestModel $model = null;

    /**
     * next
     *
     * @var mixed
     */
    protected $next = null;

    /**
     * routesNamespace
     *
     * @var mixed
     */
    protected $routesNamespace = null;

    /**
     * gateway
     *
     * @var mixed
     */
    protected $gateway = null;

    /**
     * requestFilters
     *
     * @var mixed
     */
    protected $requestFilters = [];

    /**
     * @var string 
     */
    protected string $nameIdentifier = '';

    /**
     * @var array 
     */
    protected array $filterAliases = [ 
        'id' => 0, 
        '__idparent' => 'p', 
        '__created' => 'c', 
        '__updated' => 'u' ,
    ];

    /**
     * __construct
     *
     * @param Entity\SynapseService $_entity
     */
    public function __construct(Entity\SynapseService $_entity)
    {
        $this->entity = $_entity;
        $this->nameIdentifier = $this->entity->synapse->name . ':' . $this->entity->name;
    }

    /**
     * routes
     *
     * @param RouteCollector $_router
     */
    public function routes(RouteCollector $_router) : void
    {
        # - For register some routes
    }

    /**
     * process
     *
     * @param ServerRequestInterface $_request
     * @param RequestHandlerInterface $_handler
     */
    public function process(ServerRequestInterface $_request, RequestHandlerInterface $_handler) : ResponseInterface
    {
        # - Launch dealing manager
        $result = $this->dm()->setModel($model = $this->getRequestModel($_request))->launch();
        # - Return response
        return $result->response;
    }

    /**
     * compile
     *
     */
    public function compile() : ?DealingManager\ResultInterface
    {
        return null;
    }

    /**
     * dispatchRoute
     *
     */
    public function dispatch() : ?DealingManager\ResultInterface
    {
        # - Is service route
        if ($this->isServiceRoute() || $this->model->initOnly()) {
            # - Dispatch route result with actions of this subject
            if (! $this->model->initOnly() && ! is_null($result = $this->compile())) {
                return $result;
            }
            # - Dispatch route result with actions of related subjects
            if (! is_null($result = $this->next->process($this->model))) {
                return $result;
            }
        # - Is form route
        } else {
            # - Dispatch route result with actions of form artificers
            $result = $this->model->formRoute(function(){
                return $this->getFormDealingManager($this->splitRouteName($this->model->getRelativeRouteName($this))->first())
                    ->setModel($this->model)
                    ->launch();
            });
            return $result;
        }

        return null;
    }

    /**
     * getNameIdentifier
     *
     */
    public function getNameIdentifier() : string
    {
        return $this->nameIdentifier;
    }

    /**
     * getIdentifier
     *
     */
    public function getIdentifier()
    {
        return $this->entity->id;
    }

    /**
     * getRoutesNamespace
     *
     */
    public function getRoutesNamespace()
    {
        return $this->routesNamespace;
    }

    /**
     * getFilters
     *
     * @param ServiceArtificerInterface|string|null $_targetArtificer
     * @param array $_attributes
     */
    public function getFilters($_targetArtificer = null, array $_attributes = null)
    {
        if (is_null($_targetArtificer)) {
            $_targetArtificer = $this;
        }

        if (is_string($_targetArtificer)) {
            $_targetArtificer = $this->sm->getServicesRepository()->findByName($targetArtificerServiceName = $_targetArtificer);
            if (is_null($_targetArtificer)) {
                throw new ServiceArtificerException(sprintf('Undefined service with name %s.', $targetArtificerServiceName));
            }
        }

        $subjectID = $this !== $_targetArtificer 
            ? $this->entity['subjectsFrom']->filter(function($_subject) use ($_targetArtificer) {
                return $_subject->serviceTo->id === $_targetArtificer->getEntity()->id;
            })->extract('id')->first()
            : 0;

        if (is_null($subjectID)) {
            throw new ServiceArtificerException(sprintf('Undefined service with name %s in subjects list of %s', $_targetArtificer->getNameIdentifier(), $this->getNameIdentifier()));
        }

        $targetAttributes = $_targetArtificer->getEntity()->synapse->attributes;

        if (is_null($_attributes)) {
            $_attributes = $this->requestFilters;
        }

        $return = [];
        $attributeAliases = $this->filterAliases;
        foreach ($_attributes as $attributeName => $value) {

            if (! in_array($attributeName, array_keys($attributeAliases)) && ! $attribute = $targetAttributes->match(['name' => $attributeName])->first()) {
                continue;
            }

            $attributeID = in_array($attributeName, array_keys($attributeAliases)) 
                ? $attributeAliases[$attributeName] 
            : $attribute->id;

            $suffix = $value instanceof TypeInterface ? $value->getTypeSuffix() : '';

            $return[sprintf('_%s_%s%s', $subjectID, $attributeID, $suffix)] = $value instanceof TypeInterface ? $value->valueToString() : $value;
        }

        return $return;
    }

    /**
     * initEnvironment
     *
     * @param callable $_callback
     */
    public function inEnvironment($_model, DealingManager\ScenarioInterface $_nextHandler, callable $_callback)
    {
        // # - Save current environment
        $currentState = $this->fixState();

        # - Set new environment
        $this->model = $_model;
        $this->next = $_nextHandler;

        # - Initialize new environment
        $this->initializeEnvironment();

        $result = $_callback($this);

        # - Rollback to current state
        $this->setState($currentState);

        # - Reinitialize current environment
        $this->initializeEnvironment(true);

        return $result;
    }

    /**
     * getSubjectsArtificers
     *
     */
    public function getSubjectsArtificers() : array
    {
        if (! $subjects = $this->entity['subjectsFrom']) {
            return [];
        }

        $return = [];
        foreach ($subjects as $subject) {
            $return[] = $this->sm->getServicesRepository()->findByID($subject->iSynapseServiceTo);
        }

        return $return;
    }

    /**
     * getFormsArtificers
     *
     */
    public function getFormsArtificers() : array
    {
        if (! $forms = $this->entity['forms']) {
            return [];
        }

        $return = [];
        foreach ($forms as $form) {
            $return[] = $this->sm->getFormsRepository()->findByID($form->id);
        }

        return $return;
    }

    /**
     * getStateSigns
     *
     */
    protected function getStateSigns() : array
    {
        return ['model', 'next', 'gateway'];
    }

    /**
     * initializeEnvironment
     *
     * @param mixed $_restoreState
     */
    protected function initializeEnvironment($_restoreState = false)
    {
        # - Initialize namespace
        $this->initRoutesNamespace($_restoreState);
        # - Initilialize gateway
        $this->initGateway($_restoreState);
        # - Initialize service filters
        $this->initFilters($_restoreState);
    }

    /**
     * initFilters
     *
     */
    protected function initFilters($_restoreState)
    {
        if (is_null($this->model)) {
            $this->requestFilters = [];
            return;
        }

        $filters = $this->model->getFiltersForSubject();
        $attributes = $this->entity->synapse->attributes;

        $subjectFilters = [];
        foreach ($attributes as $attribute) {
            if (! isset($filters[$attribute->id])) {
                continue;
            }
            $subjectFilters[$attribute->name] = $filters[$attribute->id];
        }

        if (isset($filters[0])) {
            $subjectFilters['id'] = $filters[0];
        }

        foreach ($this->filterAliases as $attr => $filter) {
            if (isset($filters[$filter])) {
                $subjectFilters[$attr] = $filters[$filter];
            }
        }

        if (! isset($subjectFilters['__idparent']) && $this->isTreeStructure() 
            && $this->model->getArtificers()->count() == 1) {
            $subjectFilters['__idparent'] = 0;
        }

        $this->requestFilters = $subjectFilters;

        # - Register filters in model if is initialize of state
        $_restoreState || $this->model->registerFilters($this->requestFilters);
    }

    /**
     * Prepare filter value
     *
     * @param $_type 
     * @param $_value 
     *
     * @return  
     */
    public function prepareFilterValue($_type, $_value)
    {
        switch(true) {
            case $_type == Datetime::class:
                return $this->prepareFilterValueDatetime($_value);
            default:
                return $_value;
        }
    }

    /**
     * Prepare filter value for datetime attribute
     *
     * @param  $_value 
     *
     * @return \Closure|null 
     */
    public function prepareFilterValueDatetime($_value): ?\Closure
    {
        $_value = explode('~', $_value, 2);

        $check = false;
        foreach ($_value as &$date) {
            if (preg_match('/^(?<year>\d{4})-(?<month>\d{1,2})-(?<day>\d{1,2})(\\T(?<hours>\d{1,2})\.(?<minutes>\d{1,2}))?$/', $date, $parsed)) {
                $date = \DateTime::createFromFormat('Y-m-d H:i:s', vsprintf('%s-%02d-%02d %02d:%02d:00', [
                    $parsed['year'],
                    (int)$parsed['month'],
                    (int)$parsed['day'],
                    (int)($parsed['hours'] ?? 23),
                    (int)($parsed['minutes'] ?? 59),
                ]));
                $check = true;
            } else {
                $date = false;
            }
        }

        if (! $check) {
            return null;
        }

        if (count($_value) == 1) {
            return function($_attr, Where $_where) use ($_value) {
                $_where->greaterThanOrEqualTo($_attr, $_value[0]->format('Y-m-d 00:00:00'))
                    ->and->lessThanOrEqualTo($_attr, $_value[0]->format('Y-m-d 23:59:59'));
            };
        } else {
            if (! $_value[0]) {
                return function($_attr, Where $_where) use ($_value) {
                    $_where->lessThanOrEqualTo($_attr, $_value[1]->format('Y-m-d H:i:59'));
                };
            } elseif (! $_value[1]) {
                return function($_attr, Where $_where) use ($_value) {
                    $_where->greaterThanOrEqualTo($_attr, $_value[0]->format('Y-m-d H:i:00'));
                };
            } elseif ($_value[0] && $_value[1]) {
                return function($_attr, Where $_where) use ($_value) {
                    $_where->greaterThanOrEqualTo($_attr, $_value[0]->format('Y-m-d H:i:00'))
                        ->and->lessThanOrEqualTo($_attr, $_value[1]->format('Y-m-d H:i:59'));
                };
            }
        }
    }

    /**
     * getArtificerScenarioClauses
     *
     */
    protected function getArtificerScenarioClauses()
    {
        if (! $subjects = $this->entity['subjectsFrom']) {
            return [];
        }

        $return = [];

        foreach ($subjects as $subject) {
            $return[] = new Artificer\ArtificerScenarioClause(
                $this->sm->getServicesRepository()->findByID($subject->iSynapseServiceTo),
                $subject
            );
        }

        return $return;
    }

    /**
     * isServiceRoute
     *
     */
    protected function isServiceRoute()
    {
        return preg_match(
            '/^' . $this->getNameIdentifier() . '#[A-z0-9_]+/',
            $this->model->getRelativeRouteName($this) ?: ''
        ) === 0;
    }

    /**
     * getFormArtificer
     *
     * @param string $_serviceFormName
     */
    public function getFormArtificer(string $_serviceFormName) : ?Artificer\Form\FormArtificerInterface
    {
        if (! $forms = $this->entity['forms']) {
            return null;
        }

        if ($form = $forms->match(['name' => $_serviceFormName])->first()) {
            return $this->sm->getFormsRepository()->findByID($form->id);
        }

        return null;
    }

    /**
     * getFormDealingManager
     *
     * @param Artificer\Form\FormArtificerInterface|string $_formArtificer
     */
    protected function getFormDealingManager($_formArtificer)
    {
        if (! is_object($_formArtificer)) {
            $_formArtificer = $this->sm->getFormsRepository()->findByName($_formArtificer);
        }

        # - Create DM for service form
        return $this->sm->dm(function($_builder) use ($_formArtificer){
            $_builder(
                new Artificer\ArtificerScenarioClause($_formArtificer),
                function($_builder) use ($_formArtificer) {
                    $_formArtificer->clauses($_builder);
                }
            );
        });
    }

}
