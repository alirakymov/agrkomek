<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Artificer;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Qore\DealingManager\ResultInterface;
use Qore\InterfaceGateway\Component\ComponentInterface;
use Qore\InterfaceGateway\InterfaceGateway;
use Qore\ORM\Gateway\Gateway;
use Qore\ORM\Gateway\GatewayInterface;
use Qore\Qore;
use Qore\Collection;
use Qore\EventManager;
use Qore\SynapseManager;
use Qore\SynapseManager\Artificer\Form\FormArtificerInterface;
use Qore\SynapseManager\Artificer\Service\Filter;
use Qore\SynapseManager\Plugin\Chain\Chain;
use Qore\SynapseManager\Plugin\PluginInterface;
use Qore\SynapseManager\Structure;
use Qore\ORM\Entity;
use Qore\DealingManager;
use Qore\Router\RouteCollector;
use Psr\Http\Message\ServerRequestInterface;

abstract class ArtificerBase implements ArtificerInterface
{
    /**
     * sm
     *
     * @var SynapseManager\SynapseManager|null
     */
    protected ?SynapseManager\SynapseManager $sm = null;

    /**
     * pipelineArtificers
     *
     * @var mixed
     */
    protected $pipelineArtificers = null;

    /**
     * init - Initialize artificer object
     *
     */
    public function init()
    {
        # - initialize artificer object actions
    }

    /**
     * subscribe - initialize artificer subscribes
     *
     */
    public function subscribe(EventManager\EventManager $_em)
    {
        # - initialize artificer subscribes
    }

    /**
     * @inheritdoc
     */
    abstract public function getNameIdentifier(): string;

    /**
     * Generate route name
     *
     * @param ArtificerInterface|string $_namespace
     * @param string $_routeName (optional)
     *
     * @return string
     */
    public function getRouteName($_namespace, string $_routeName = null) : string
    {
        if (is_null($_routeName)) {
            $_routeName = $_namespace;
            $_namespace = null;
        }

        if (is_object($_namespace)) {
            $_namespace = get_class($_namespace);
        }

        if (is_null($_namespace)) {
            $_namespace = $this->routesNamespace;
        }

        $_namespace ??= static::class;

        return $_namespace . '.' . $_routeName;
    }

    /**
     * setSynpaseManager
     *
     * @param SynapseManager\SynapseManager $_sm
     */
    public function setSynapseManager(SynapseManager\SynapseManager $_sm) : void
    {
        $this->sm = $_sm;
    }
    /**
     * getSynapseManager
     *
     */
    public function getSynapseManager() : SynapseManager\SynapseManager
    {
        return $this->sm;
    }

    /**
     * getRequestModel
     *
     * @param ServerRequestInterface $_request
     */
    public function getRequestModel(ServerRequestInterface $_request = null) : RequestModel
    {
        $model = new RequestModel();
        $model->setRequest($_request);
        return $model;
    }

    /**
     * Generate RequestDataSource instance
     *
     * @param \Psr\Http\Message\ServerRequestInterface|null $_request (optional)
     * @param FormArtificerInterface|null $_artificer (optional)
     *
     * @return Form\DataSource\RequestDataSource
     */
    public function getRequestDataSource(ServerRequestInterface $_request = null, FormArtificerInterface $_artificer = null) : Form\DataSource\RequestDataSource
    {
        return new Form\DataSource\RequestDataSource(
            $_request ?? $this->model->getRequest(),
            $this->sm,
            $_artificer ?? $this
        );
    }

    /*
     * Generate EntityDataSource instance
     *
     * @param $_entities
     *
     * @return Form\DataSource\EntityDataSource
     */
    public function getEntityDataSource($_entities) : Form\DataSource\EntityDataSource
    {
        return new Form\DataSource\EntityDataSource($_entities);
    }

    /**
     * Generate and return instance of Result
     *
     * @param ResponseInterface|array|null $_response (optional)
     *
     * @return DealingManager\Result
     */
    public function response($_response = null) : DealingManager\Result
    {
        $isComposable = function ($_object) {
            return (is_a($_object, InterfaceGateway::class) || is_a($_object, ComponentInterface::class));
        };

        if ($isComposable($_response)) {
            $_response = [$_response];
        }

        if (is_array($_response)) {
            $result = [];
            foreach ($_response as $item) {
                if (is_object($item) && $isComposable($item)) {
                    $result = array_merge($result, $item->compose());
                }
            }
            $_response = new JsonResponse($result);
        }

        return $this->getResponseResult(['response' => $_response]);
    }

    /**
     * getResponseResult
     *
     * @param array $_data
     */
    public function getResponseResult(array $_data = []) : DealingManager\Result
    {
        return new DealingManager\Result($_data);
    }

    /**
     * getModel
     *
     */
    public function getModel() : ?RequestModel
    {
        return $this->model;
    }

    /**
     * Build DealingManager chain
     *
     * @param $_target (optional)
     * @param $_model (optional)
     *
     * @return DealingManager\DealingManager
     */
    public function dm($_target = null, $_model = null) : DealingManager\DealingManager
    {
        if (is_null($_target)) {
            $_target = $this;
        }

        $artificer = null;
        if (is_object($_target) && ($_target instanceof Form\FormArtificerInterface || $_target instanceof Service\ServiceArtificerInterface)) {
            $artificer = $_target;
        } else {
            # - is service artificer
            if (preg_match('/^[A-z0-9_]+:[A-z0-9_]+$/', $_target)) {
                $artificer = $this->sm->getServicesRepository()->findByName($_target);
            # - is form artificer
            } elseif (preg_match('/^[A-z0-9_]+:[A-z0-9_]+#[A-z0-9_]+$/', $_target)) {
                $artificer = $this->sm->getFormsRepository()->findByName($_target);
            }
        }

        if (is_null($artificer)) {
            throw new ArtificerException(sprintf("Unknown type of target (%s) for build Dealing process", is_object($_target) ? get_class($_target) : $_target ));
        }

        # - Create DM for target and register all clauses of related subjects with this artificer
        $dm = $this->sm->dm(function($_builder) use ($artificer){
            $_builder(
                new ArtificerScenarioClause($artificer),
                function($_builder) use ($artificer) {
                    $artificer->clauses($_builder);
                }
            );
        });

        # - Prepare model
        if (is_null($_model)) {
            $_model = $this->model ?? $this->getRequestModel();
        }

        if ($artificer instanceof Form\FormArtificerInterface) {
            $this->prepareFormModel($artificer, $_model);
        }

        if ($artificer instanceof Service\ServiceArtificerInterface) {
            $this->prepareServiceModel($artificer, $_model);
        }

        # - set model and return DM
        return $dm->setModel($_model);
    }

    /**
     * mm
     *
     */
    public function mm($_entityName = null, $_entityData = null)
    {
        if (is_null($_entityData)) {
            $_entityData = $_entityName;
            $_entityName = null;
        }

        if (is_null($_entityName)) {
            $_entityName = $this->getSynapseEntityName();
        }

        return is_array($_entityData)
            ? $this->sm->mm($_entityName, $_entityData)
            : $this->sm->mm($_entityData ?? $_entityName);
    }

    /**
     * sm
     *
     */
    public function sm(string $_artificer = null)
    {
        $sm = $this->sm;
        return is_null($_artificer) ? $sm : $sm($_artificer);
    }

    /**
     * Set model
     *
     * @param RequestModel $_model
     *
     * @return ArtificerInterface
     */
    public function setModel(RequestModel $_model): ArtificerInterface
    {
        $this->model = $_model;
        return $this;
    }

    /**
     * normalizeFilters
     *
     * @param Collection\Collection $_filters
     */
    public function normalizeFilters(Collection\Collection $_filters)
    {
        return $this->model->getFilters(true)->reduce(function($_result, $_artificerFilter){
            foreach ($_artificerFilter['filters'] as $attribute => $value) {
                $_result[$_artificerFilter['referencePath'] . '.' . $attribute] = $value;
            }
            return $_result;
        }, []);
    }

    /**
     * presentAs
     *
     * @param mixed $_class
     * @param mixed $_options
     */
    public function presentAs($_class, $_options = [])
    {
        return Qore::service($_class)->initialize($this, $_options);
    }

    /**
     * getGateway
     *
     */
    public function getGateway()
    {
        return $this->gateway
            ?? ($this->gateway = $this->sm->mm($this->getSynapseEntityName()));
    }

    /**
     * Get gateway for current artificer
     *
     * @param array|bool|null $_filters (optional)
     *
     * @return \Qore\ORM\Gateway\Gateway
     */
    public function gateway($_filters = null) : Gateway
    {
        if (is_null($this->gateway) # - if gateway is't initialized
            || ! $this->gateway->getProcessor()->isRootProcessor() # - test this alternative variant for check if it's relative artificer
        ) {
            $gw = $this->sm->mm(
                $this instanceof Service\ServiceArtificerInterface
                ? 'SM:' . $this->entity->synapse->name
                : 'SM:' . $this->entity->service->synapse->name
            );
        } else {
            $gw = clone $this->gateway;
        }

        if ($_filters === true) {
            $_filters = $this->model->getFilters(true)->reduce(function($_result, $_artificerFilter){
                foreach ($_artificerFilter['filters'] as $attribute => $value) {
                    $_result[$_artificerFilter['referencePath'] . '.' . $attribute] = $value;
                }
                return $_result;
            }, []);
        } elseif (is_null($_filters)) {
            return $gw;
        }

        return $gw->where(function($_where) use ($_filters) {
            foreach ($_filters as $attribute => $value) {
                if ($value instanceof Filter) {
                    $value($_where, $attribute);
                } else {
                    $_where([$attribute => $value]);
                }
            }
        }, '@root');
    }

    /**
     * Return initialized local gateway
     *
     * @param $_filters (optional)
     * @param RequestModel $_model (optional)
     *
     * @return \Qore\ORM\Gateway\GatewayInterface
     */
    public function getLocalGateway($_filters = null, RequestModel $_model = null) : GatewayInterface
    {
        if (is_null($_model) && $_filters instanceof RequestModel) {
            $_model = $_filters;
            $_filters = null;
        }

        $_model ??= $this->getRequestModel(! is_null($this->model) ? $this->model->getRequest() : null);
        $this->dm()->launch($_model->initOnly(true));
        $gw = $_model->getGateways(false)->first();
        return is_null($_filters) ? $gw : $gw->where(function($_where) use ($_filters) {
            foreach ($_filters as $attribute => $value) {
                if ($value instanceof Filter) {
                    $value($_where, $attribute);
                } else {
                    $_where([$attribute => $value]);
                }
            }
        }, '@root');
    }

    /**
     * getPreffix - return preffix for events of current artificer
     *
     * Examples:
     *  For global events:
     *      ServiceArtificer: 'SynapseName:ServiceName'
     *      FormArtificer:    'SynapseName:ServiceName#FormName'
     *  For local events:
     *      ServiceArtificer: '/SynapseName:ServiceName/NextSynapseName:NextServiceName'
     *      FormArtificer:    '/SynapseName:ServiceName#FormName/NextSynapseName:NextServiceName#NextFormName'
     *
     * @param mixed $_type (1 - get global event | 2 - get local event)
     */
    public function getPreffix(int $_type = self::GLB)
    {
        $preffix = null;

        if ($_type === self::GLB) {
            $preffix = $this->getNameIdentifier();
        } elseif ($_type === self::LCL) {
            $preffix = '/' . $this instanceof Service\ServiceArtificerInterface
                ? $this->routesNamespace
                : $this->fieldsNamespace;
        }

        return $preffix;
    }

    /**
     * getEntity
     *
     */
    public function getEntity() : Entity\EntityInterface
    {
        return $this->entity;
    }

    /**
     * getSynapseEntityName
     *
     */
    public function getSynapseEntityName()
    {
        return $this instanceof Service\ServiceArtificerInterface
            ? 'SM:' . $this->entity->synapse->name
            : 'SM:' . $this->entity->service->synapse->name;
    }

    /**
     * splitRouteName
     *
     * @param string $_routeName
     */
    public function splitRouteName(string $_routeName = null) : Collection\Collection
    {
        $_routeName = $_routeName ?? $this->routeNamespace;
        return new Collection\Collection(explode('.', $_routeName));
    }

    /**
     * pipelineEnvironment
     *
     * @param Collection\Collection $_pipelineArtificers
     * @param \Closure $_callback
     */
    public function pipelineEnvironment(Collection\Collection $_pipelineArtificers, \Closure $_callback)
    {
        $currentPipelineArtificers = $this->pipelineArtificers;
        $this->pipelineArtificers = $_pipelineArtificers;

        $_callback($this);

        $this->pipelineArtificers = $currentPipelineArtificers;
    }

    /**
     * clauses
     *
     * @param DealingManager\ScenarioBuilder $_builder
     */
    public function clauses(DealingManager\ScenarioBuilder $_builder) : void
    {
        $pipelineArtificers = is_null($this->pipelineArtificers)
            ? new Collection\Collection([$this])
            : $this->pipelineArtificers->appendItem($this);

        $clauses = $this->getArtificerScenarioClauses();
        foreach ($clauses as $clause) {
            # - Collect names of routes artificers
            $pipelineNameIdentifiers = $pipelineArtificers->map(function($_artificer){
                return $_artificer->getNameIdentifier();
            });
            # - Bypass the loop
            if ($pipelineNameIdentifiers->contains($clause->getArtificer()->getNameIdentifier())) {
                continue;
            }
            # - Register subscenarios
            $_builder($clause, function($_builder) use ($clause, $pipelineArtificers) {
                $clause->getArtificer()->pipelineEnvironment($pipelineArtificers, function($_artificer) use ($_builder) {
                    $_artificer->clauses($_builder);
                });
            });
        }
    }

    /**
     * registerSubjectsRoutes
     *
     * @param RouteCollector $_router
     */
    public function registerSubjectsRoutes(RouteCollector $_router) : void
    {
        $pipelineArtificers = is_null($this->pipelineArtificers)
            ? new Collection\Collection([$this])
            : $this->pipelineArtificers->appendItem($this);

        foreach ($this->getSubjectsArtificers() as $subject) {
            # - Collect names of routes artificers
            $pipelineNameIdentifiers = $pipelineArtificers->map(function($_artificer){
                return $_artificer->getNameIdentifier();
            });
            # - Bypass the loop
            if ($pipelineNameIdentifiers->contains($subject->getNameIdentifier())) {
                continue;
            }
            # - Register routes
            # - Dilemma: Стоит ли назначать автоматический преффикс группы для роутов связанных субъектов [group preffix : '/' . $this->getRouteIdentifier()]
            $_router->group($this->getRouteIdentifier(), $subject->getNameIdentifier(), function($_router) use ($subject, $pipelineArtificers) {
                $subject->pipelineEnvironment($pipelineArtificers, function($_subject) use ($_router) {
                    $_subject->routes($_router);
                });
            });
        }
    }

    /**
     * registerFormsRoutes
     *
     * @param RouteCollector $_router
     */
    public function registerFormsRoutes(RouteCollector $_router) : void
    {
        $pipelineArtificers = is_null($this->pipelineArtificers)
            ? new Collection\Collection([$this])
            : $this->pipelineArtificers->appendItem($this);

        # - Register routes
        foreach ($this->getFormsArtificers() as $form) {
            # - Collect names of routes artificers
            $pipelineNameIdentifiers = $pipelineArtificers->map(function($_artificer){
                return $_artificer->getNameIdentifier();
            });
            # - Bypass the loop
            if ($pipelineNameIdentifiers->contains($form->getNameIdentifier())) {
                continue;
            }
            # - Register routes
            $_router->group('', $form->getNameIdentifier(), function($_router) use ($form, $pipelineArtificers) {
                $form->pipelineEnvironment($pipelineArtificers, function($_form) use ($_router) {
                    $_form->routes($_router);
                });
            });
        }
    }

    /**
     * Return created and initialized instance of requested plugin
     *
     * @param string $_name ClassName of plugin
     *
     * @return \Qore\SynapseManager\Plugin\PluginInterface
     */
    public function plugin(string $_name) : PluginInterface
    {
        $plugin = $this->sm->getPlugin($_name);
        $plugin->setArtificer($artificer ?? $this);
        return $plugin;
    }

    /**
     * getRouteIdentifier
     *
     */
    public function getRouteIdentifier()
    {
        return '/' . str_replace('#', '-', $this->getNameIdentifier());
    }

    /**
     * isServiceArtificer
     *
     */
    public function isServiceArtificer() : bool
    {
        return $this instanceof Service\ServiceArtificerInterface;
    }

    /**
     * isTreeStructure
     *
     */
    public function isTreeStructure()
    {
        return $this instanceof Service\ServiceArtificerInterface
            ? (int)$this->entity->synapse->tree === 1
            : (int)$this->entity->service->synapse->tree === 1;
    }

    /**
     * serialize
     *
     */
    public function serialize()
    {

    }

    /**
     * unserialize
     *
     */
    public function unserialize()
    {

    }

    /**
     * fixState
     *
     */
    protected function fixState()
    {
        $state = [];
        foreach ($this->getStateSigns() as $sign) {
            $state[$sign] = $this->{$sign};
            $this->{$sign} = null;
        }
        return $state;
    }

    /**
     * setState
     *
     * @param array $_state
     */
    protected function setState(array $_state)
    {
        $state = [];
        foreach ($this->getStateSigns() as $sign) {
            $this->{$sign} = $_state[$sign];
        }
        return $state;
    }

    /**
     * initRoutesNamespace
     *
     * @param mixed $_restoreState
     */
    protected function initRoutesNamespace($_restoreState)
    {
        if (is_null($this->model)) {
            $this->routesNamespace = null;
            return;
        }

        $artificers = $this->model->getArtificers();
        if (! $artificers->count()) {
            $this->routesNamespace = static::class;
            return;
        }

        $this->routesNamespace = get_class($artificers->first());
        if ($artificers->count() > 1) {
            $this->routesNamespace .= '.' . $artificers->skip(1)->reduce(function ($_ns, $artificer) {
                return ($_ns ? $_ns . '.' : '') . $artificer->getNameIdentifier();
            }, '');
        }
    }

    /**
     * initGateway
     *
     */
    protected function initGateway($_restoreState)
    {
        # - Break if is restore state initialize
        if ($_restoreState) {
            return;
        }

        $this->gateway = null;

        # - Continue if model is null
        if (is_null($this->model)) { #! is_null($this->gateway) || is_null($this->model)
            return;
        }

        # - Get parent gateway or initialize current gateway
        $gw = $this->model->getArtificers()->count() > 1
            ? $this->model->getArtificers()->takeLast(2)->first()->getGateway()
            : $this->getGateway();

        # - If gateway is initialized
        if (! is_null($this->gateway)) {
            $this->model->registerGateway(null, $this->gateway);
            return;
        }

        if (is_null($subject = $this->model->getSubjects()->last())) {
            return;
        }

        $referenceName = $subject instanceof Structure\Entity\SynapseServiceFormField
            ? $subject->relatedSubject->getReferenceName()
            : $subject->getReferenceName();

        $gw->with($referenceName, function($_gw) {
            $this->gateway = $_gw;
        });

        # - Register gateway in model if is initialize of state
        $this->model->registerGateway($referenceName, $this->gateway);
    }

    /**
     * prepareFormModel
     *
     * @param ArtificerInterface $_artificer
     * @param RequestModel $_model
     */
    protected function prepareFormModel(ArtificerInterface $_artificer, RequestModel $_model)
    {
    }

    /**
     * prepareServiceModel
     *
     * @param ArtificerInterface $_artificer
     * @param RequestModel $_model
     */
    protected function prepareServiceModel(ArtificerInterface $_artificer, RequestModel $_model)
    {
    }

    /**
     * Dispatch
     *
     * @return ResultInterface|null
     */
    public function dispatch(): ?ResultInterface
    {
        return null;
    }

}
