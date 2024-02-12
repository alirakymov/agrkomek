<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\AmadeusDecoder\Manager;

use Laminas\Db\Sql\Expression;
use Mezzio\Helper\UrlHelper;
use Qore\DealingManager\Result;
use Qore\DealingManager\ResultInterface;
use Qore\Form\Decorator\QoreFront;
use Qore\InterfaceGateway\Component\Modal;
use Qore\InterfaceGateway\InterfaceGateway;
use Qore\Qore as Qore;
use Qore\Router\RouteCollector;
use Qore\SynapseManager\Artificer\Decorator\ListComponent;
use Mezzio\Template\TemplateRendererInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Qore\SynapseManager\Artificer\Service\ServiceArtificer;
use Qore\SynapseManager\Plugin\FormMaker\FormMaker;
use Qore\SynapseManager\Plugin\RoutingHelper\RoutingHelper;

/**
 * Class: AmadeusDecoderService
 *
 * @see Qore\SynapseManager\Artificer\Service\ServiceArtificer
 */
class AmadeusDecoderService extends ServiceArtificer
{
    /**
     * sortable
     *
     * @var mixed
     */
    private $sortable = false;

    /**
     * @var int
     */
    private $limit = 25;

    /**
     * serviceForm
     *
     * @var string
     */
    private $serviceForm = 'AmadeusDecoderForm';

    /**
     * @var \Qore\SynapseManager\Plugin\RoutingHelper\RoutingHelper
     */
    private RoutingHelper $routingHelper;

    /**
     * routes
     *
     * @param RouteCollector $_router
     */
    public function routes(RouteCollector $_router) : void
    {
        $this->routingHelper = $this->plugin(RoutingHelper::class);
        $_router->group('/amadeus-decoder', null, function($_router) {
            $this->routingHelper->routesCrud($_router);
        });
        # - Register related subjects routes
        $this->registerSubjectsRoutes($_router);
    }

    /**
     * compile
     *
     */
    public function compile() : ?ResultInterface
    {
        /** @var RoutingHelper */
        $this->routingHelper = $this->plugin(RoutingHelper::class);
        list($method, $arguments) = $this->routingHelper->dispatch() ?? [null, null];

        return ! is_null($method) ? call_user_func_array([$this, $method], $arguments ?? []) : null;
    }

    /**
     * Get order option name in options storage array
     *
     * @return string
     */
    public function getOrderOptionName() : string
    {
        return sprintf('%s-order', $this->entity->synapse()->name);
    }

    /**
     * index
     *
     * @param  mixed $_reload
     * @return void
     */
    protected function index($_reload = false)
    {
        $this->next->process($this->model);
        $request = $this->model->getRequest();

        $component = $this->getComponent(true);
        $ig = Qore::service(InterfaceGateway::class);

        if ($request->isXmlHttpRequest()) {
            return $this->response(
                [ $_reload ? $component : $ig('layout')->component($component) ]
            );
        } else {
            return $this->response(new HtmlResponse(Qore::service(TemplateRendererInterface::class)->render('app::main', [
                'title' => 'Создание - Создание шаблонов и паттернов',
                'frontProtocol' => $ig('layout')->component($component)->compose(),
            ])));
        }
    }
    
    /**
     * reorder
     *
     * @return void
     */
    protected function reorder()
    {
        $this->next->process($this->model);

        $request = $this->model->getRequest();
        $component = $this->getComponent();

        if ($request->getMethod() === 'POST'
            && $this->sortable && ! is_null($storage = $this->getOptionsStorage())) {
            # - Save data
            $requestData = $request->parseJsonBody();
            if (isset($requestData['data'])) {
                $storage['__options'] = array_merge($storage['__options'] ?? [], [
                    $this->getOrderOptionName() => $requestData['data']
                ]);
                $this->mm($storage)->save();
            }
        }

        return $this->response();
    }
    
    /**
     * create
     *
     * @return void
     */
    protected function create()
    {
        # - Init synpase structure
        $this->next->process($this->model);

        $request = $this->model->getRequest();

        /** @var FormMaker */
        $formMaker = $this->plugin(FormMaker::class);
        $fm = $formMaker->make($this->serviceForm);

        $ig = Qore::service(InterfaceGateway::class);
        $modal = $ig(Modal::class, sprintf('%s.%s', get_class($this), 'modal-create'))
            ->setTitle('Создание')
            ->component(Qore::service(QoreFront::class)->decorate($fm));

        $component = $this->getComponent();

        if ($request->getMethod() === 'POST') {
            if ($fm->isValid()) {
                # - Save data
                $this->mm($this->model->getDataSource()->extractData()->first())->save();

                # - Generate json response
                return $this->response([
                    $modal->execute('close'),
                    $component->execute('reload'),
                ]);
            } else {
                return $this->response($fm->decorate(['decorate']));
            }
        } else {
            $modal->execute('open');
            # - Generate json response
            return $this->response($ig('layout')->component($modal));
        }
    }

  
    /**
     * update
     *
     * @return void
     */
    protected function update()
    {
        # - Init synpase structure
        $this->next->process($this->model);

        $request = $this->model->getRequest();

        /** @var FormMaker */
        $formMaker = $this->plugin(FormMaker::class);
        $fm = $formMaker->make($this->serviceForm);

        $ig = Qore::service(InterfaceGateway::class);
        $modal = $ig(Modal::class, sprintf('%s.%s', get_class($this), 'modal-update'))
            ->setTitle('Редактирование')
            ->component(Qore::service(QoreFront::class)->decorate($fm));

        $component = $this->getComponent();

        if ($request->getMethod() === 'POST') {
            if ($fm->isValid()) {
                # - Save data
                $this->model->getDataSource()->extractData()->each(function($_entity){
                    $this->mm($_entity)->save();
                });
                # - Generate json response
                return $this->response([
                    $modal->execute('close'),
                    $component->execute('reload'),
                ]);
            } else {
                return $this->response(
                    $fm->decorate(['decorate'])
                );
            }
        } else {
            $modal->execute('open');
            # - Generate json response
            return $this->response($ig('layout')->component($modal));
        }
    }

    
    /**
     * delete
     *
     * @return void
     */
    protected function delete()
    {
        $routeResult = $this->model->getRouteResult();
        $routeParams = $routeResult->getMatchedParams();

        $object = $this->gateway([
            '@this.id' => $routeParams['id']
        ])->one();

        ! is_null($object) && $this->mm($object)->delete();

        $component = $this->getComponent();
        return $this->response([$component->execute('reload')]);
    }


    /**
     * default
     *
     * @return void
     */
    protected function default()
    {
        $this->next->process($this->model);

        return new Result([
            'response' => $this->getComponent(true)
        ]);
    }

    
    /**
     * getComponent
     *
     * @param  mixed $_data
     * @return void
     */
    protected function getComponent($_data = null)
    {
        # - Формируем уникальный суффикс для имени компонента интерфейса
        $testFilters = $this->model->getFilters(true)->firstMatch([
            'referencePath' => '{relation.path}' # Example: {relation.path} => @this.id
        ]);
        
        if ($_data === true) {
            $filters = $this->model->getFilters(true)->reduce(function($_result, $_artificerFilter){
                foreach ($_artificerFilter['filters'] as $attribute => $value) {
                    $_result[$_artificerFilter['referencePath'] . '.' . $attribute] = $value;
                }
                return $_result;
            }, []);

            if ($this->isTreeStructure() && ! isset($filters['@this.__idparent'])) {
                $filters['@this.__idparent'] = 0;
            }

            $_data = $this->gateway($filters)->select(function($_select) {
                $queryParams = $this->model->getRequest()->getQueryParams();
                $_select->offset($this->limit * ((int)($queryParams['page'] ?? 1) - 1))
                    ->limit($this->limit);
            })->all();
        }

        return $this->presentAs(ListComponent::class, [
            'actions' => $this->getListActions(),
            'suffix' => $testFilters['filters']['id'] ?? null,
            'sortable' => $this->getSortableOptions(),
            'pagination' => [
                'count' => $this->getCount(),
                'page' => (int)($queryParams['page'] ?? 1),
                'per-page' => $this->limit,
            ]
        ])->build($_data);
    }

    /**
     * return count of products
     *
     * @return int
     */
    protected function getCount() : int
    {
        $filters = $this->model->getFilters(true)->reduce(function($_result, $_artificerFilter){
            foreach ($_artificerFilter['filters'] as $attribute => $value) {
                $_result[$_artificerFilter['referencePath'] . '.' . $attribute] = $value;
            }
            return $_result;
        }, []);

        if ($this->isTreeStructure() && ! isset($filters['@this.__idparent'])) {
            $filters['@this.__idparent'] = 0;
        }

        return (int)$this->gateway($filters)->select(function($_select) {
            $_select->columns(['@this.count' => new Expression('count(*)')])
                ->limit(1);
        })->all()->extract('count')->first();
    }

    /**
     * getListActions
     *
     */
    protected function getListActions()
    {
        
        $actions = [
            'document-patterns' => [
                'label' => 'Паттерны документа',
                'icon' => 'fa fa-briefcase',
                'actionUri' => function($_data) {
                    $artificer = $this->sm->getServicesRepository()->findByName('AmadeusDecoderPattern:Manager');
                    return Qore::service(UrlHelper::class)->generate(
                        $this->getRouteName(get_class($artificer), 'index'),
                        [],
                        $artificer->getFilters('AmadeusDecoder:Manager', ['id' => $_data['id']])
                    );
                }
            ],
            'document-template' => [
                'label' => 'Шаблон документа',
                'icon' => 'fa fa-file',
                'actionUri' => function($_data) {
                    $artificer = $this->sm->getServicesRepository()->findByName('AmadeusDecoderTemplate:Manager');
                    return Qore::service(UrlHelper::class)->generate(
                        $this->getRouteName(get_class($artificer), 'index'),
                        [],
                        $artificer->getFilters('AmadeusDecoder:Manager', ['id' => $_data['id']])
                    );
                }
            ],
            'update', 'delete'
        ];

        return $actions;
        

        return [];
    }

    /**
     * getSortableOptions
     *
     */
    protected function getSortableOptions()
    {
        if (! $this->sortable) {
            return false;
        }

        $storage = $this->getOptionsStorage();
        return $storage['__options'][$this->getOrderOptionName()] ?? [];
    }

    /**
     * getOptionsStorage
     *
     */
    protected function getOptionsStorage()
    {
        $optionsStorage = null;
        if (isset($this->requestFilters['__idparent'])) {
            $optionsStorage = $this->getLocalGateway(['id' => $this->requestFilters['__idparent']])->one();
        } else  {
            /**
                $filter = $this->model->getFilters(true)->firstMatch([
                    'namespace' => sprintf('%s.%s', $this->getNameIdentifier(), '{RelatedSynapse:Service}'),
                ]);

                if (isset($filter['filters']['id'])) {
                    $optionsStorage = $this->mm('{RelatedSynapse}')->where(function($_where) use ($filter) {
                        $_where(['id' => $filter['filters']['id']]);
                    })->one();
                }
            */
        }

        return $optionsStorage;
    }

}
