<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\Partner\Manager;

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
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Qore\Form\FormManager;
use Qore\ImageManager\ImageManager;
use Qore\SynapseManager\Artificer\Service\ServiceArtificer;
use Qore\SynapseManager\Plugin\FormMaker\FormMaker;
use Qore\SynapseManager\Plugin\RoutingHelper\RoutingHelper;

/**
 * Class: PartnerService
 *
 * @see Qore\SynapseManager\Artificer\Service\ServiceArtificer
 */
class PartnerService extends ServiceArtificer
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
    private $serviceForm = 'PartnerForm';

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
        $_router->group('/partner', null, function($_router) {
            $this->routingHelper->routesCrud($_router);
            $_router->any('/upload/{id:\d+}', 'upload');
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
        list($method, $arguments) = $this->routingHelper->dispatch(['upload']) ?? [null, null];

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
                'title' => 'Сервис создания клиентов - Сервис создания клиентов',
                'frontProtocol' => $ig('layout')->component($component)->compose(),
            ])));
        }
    }

    /**
     * reorder
     *
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
     * upload
     *
     */
    protected function upload()
    {
        # - Init synpase structure
        $this->next->process($this->model);

        $request = $this->model->getRequest();
        $routeResult = $this->model->getRouteResult();
        $routeParams = $routeResult->getMatchedParams();

        $partner = $this->mm()->where(function($_where) use ($routeParams) {
            $_where(['@this.id' => $routeParams['id']]);
        })->one();

        if (! $partner) {
            return $this->response([]);
        }

        /** @var InterfaceGateway */
        $ig = Qore::service(InterfaceGateway::class);

        $fm = $this->getUploadForm();

        $modal = $ig(Modal::class, 'upload image') 
            ->setTitle('Загрузка логотипа клиентов')
            ->component($fm->decorate('decorate'));

        if ($request->getMethod() === 'POST') {
            # - Upload files
            $files = $this->model->getRequest()->getUploadedFiles();

            if (isset($files['file'])) {
                $partner['file'] = current($files['file']);
                $this->mm($partner)->save();
            }
            $modal->execute('close');
            # - Generate json response
            return $this->response($modal);

        } else {

            $modal->execute('open');
            # - Generate json response
            return $this->response($ig('layout')->component($modal));
        }
    }

    /**
     * Generate upload form
     *
     * @return \Qore\Form\FormManager 
     */
    private function getUploadForm(): FormManager
    {
        $routeResult = $this->model->getRouteResult();

        return Qore::service(FormManager::class)(
            # - Form name
            'images-upload-form',
            # - Form action
            Qore::service(UrlHelper::class)->generate(
                $this->getRouteName('upload'),
                $routeResult->getMatchedParams(),
                $this->model->getSubjectFilters()
            ),
            # - Form fields
            [
                'images' => [
                    'type' => \Qore\Form\Field\Dropzone::class,
                    'label'=> 'Загрузка файлов',
                    'placeholder' => 'Перетащите файлы в эту область',
                    'info' => '',
                    'additional' => [
                        'queuecomplete' => Qore::service(UrlHelper::class)->generate(
                            $this->getRouteName('upload'),
                            $routeResult->getMatchedParams(),
                            $this->model->getSubjectFilters()
                        ),
                    ]
                ],
            ]
        );
    }

    /**
     * runDelete
     *
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
     * Default action process
     *
     * @return ResultInterface
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
     * @param mixed $_data
     */
    protected function getComponent($_data = null)
    {
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
            'columns' => [
                'id' => [
                    'label' => '#',
                    'class-header' => 'col-1 text-center',
                    'class-column' => 'col-1 text-center',
                ],
                'uniqid' => [
                    'label' => 'Лого',
                    'class-header' => 'text-center',
                    'class-column' => 'text-center',
                    'transform' => function($_partner) {
                        /** @var ImageManager */
                        $logotype = $_partner->logotype;
                        return $logotype ? [ 'image' => $logotype->setSize(500, 500)->getUri(), 'width' => '50', 'height' => '50', ] : null;
                    },
                ],
                'name' => [
                    'label' => 'Клиент',
                    'class-header' => 'col-3',
                    'class-column' => 'col-3',
                ],
                'created' => [
                    'label' => 'Дата cоздания',
                    'class-header' => 'col-2',
                    'class-column' => 'col-2',
                    'transform' => function($_item) {
                        return $_item['__created']->format('d.m.Y H:i');
                    }
                ],
                'description' => [
                    'label' => 'Описание',
                    'class-header' => 'col',
                    'class-column' => 'col',
                ],
            ],
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
            'logotype' => [
                'label' => 'Логотип',
                'icon' => 'far fa-image',
                'actionUri' => function($_data) {
                    return Qore::service(UrlHelper::class)->generate(
                        $this->getRouteName('upload'),
                        ['id' => $_data['id']],
                    );
                }
            ],
            'partner-email' => [
                'label' => 'Выделенная почта клиента',
                'icon' => 'far fa-at',
                'actionUri' => function($_data) {
                    $artificer = $this->sm->getServicesRepository()->findByName('PartnerEmail:Manager');
                    return Qore::service(UrlHelper::class)->generate(
                        $this->getRouteName(get_class($artificer), 'index'),
                        [],
                        $artificer->getFilters('Partner:Manager', ['id' => $_data['id']])
                    );
                }
            ],
            'partner-criteria' => [
                'label' => 'Критерии к почте',
                'icon' => 'fas fa-filter',
                'actionUri' => function($_data) {
                    $artificer = $this->sm->getServicesRepository()->findByName('PartnerCriteria:Manager');
                    return Qore::service(UrlHelper::class)->generate(
                        $this->getRouteName(get_class($artificer), 'index'),
                        [],
                        $artificer->getFilters('Partner:Manager', ['id' => $_data['id']])
                    );
                }
            ],
            'outbox-email' => [
                'label' => 'Почта клиента',
                'icon' => 'fas fa-mail-bulk',
                'actionUri' => function($_data) {
                    $artificer = $this->sm->getServicesRepository()->findByName('OutboxPartnerEmail:Manager');
                    return Qore::service(UrlHelper::class)->generate(
                        $this->getRouteName(get_class($artificer), 'index'),
                        [],
                        $artificer->getFilters('Partner:Manager', ['id' => $_data['id']])
                    );
                }
            ],
            'update', 'delete'
        ];

        return $actions;
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
