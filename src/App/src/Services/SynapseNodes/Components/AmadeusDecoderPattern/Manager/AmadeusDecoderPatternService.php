<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\AmadeusDecoderPattern\Manager;

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
use Mezzio\Helper\UrlHelper;
use Qore\App\SynapseNodes\Components\AmadeusDecoderPattern\AmadeusDecoderPattern;
use Qore\Form\Field\Select;
use Qore\Form\Field\Submit;
use Qore\Form\Field\Text;
use Qore\Form\FormManager;
use Qore\SynapseManager\Artificer\Service\ServiceArtificer;
use Qore\SynapseManager\Plugin\FormMaker\FormMaker;
use Qore\SynapseManager\Plugin\RoutingHelper\RoutingHelper;

/**
 * Class: AmadeusDecoderPatternService
 *
 * @see Qore\SynapseManager\Artificer\Service\ServiceArtificer
 */
class AmadeusDecoderPatternService extends ServiceArtificer
{
    /**
     * sortable
     *
     * @var mixed
     */
    private $sortable = false;

    /**
     * serviceForm
     *
     * @var string
     */
    private $serviceForm = 'AmadeusDecoderPatternForm';

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
        $_router->group('/amadeus-decoder-pattern', null, function($_router) {
            $this->routingHelper->routesCrud($_router);
            $_router->any('/glossaries/{id:\d+}', 'glossaries');
            $_router->any('/define-target-group/{id:\d+}', 'define-target-group');
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
        list($method, $arguments) = $this->routingHelper->dispatch(['glossaries','define-target-group' => 'defineTargetGroup']) ?? [null, null];

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
                'title' => 'Регулярные выражение документа - Работа с паттернами',
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
     * Glossaries form action
     *
     * @return ?ResultInterface
     */
    protected function glossaries(): ?ResultInterface
    {
        # - Init synpase structure
        $this->next->process($this->model);

        $request = $this->model->getRequest();
        $routeResult = $this->model->getRouteResult();
        $routeParams = $routeResult->getMatchedParams();

        $pattern = $this->mm('SM:AmadeusDecoderPattern')
            ->where(['@this.id' => $routeParams['id']])
            ->one();

        if (is_null($pattern)) {
            return $this->response([]);
        }

        $fm = $this->getPatternGlossariesForm($pattern);
        if (is_null($fm)) {
            return $this->response([]);
        }

        $ig = Qore::service(InterfaceGateway::class);
        $modal = $ig(Modal::class, sprintf('%s.%s', get_class($this), 'modal-update'))
            ->setTitle('Редактирование')
            ->component(Qore::service(QoreFront::class)->decorate($fm));

        if ($request->getMethod() === 'POST') {
            if ($fm->isValid()) {
                $data = $request->getParsedBody();
                $pattern->groups = $data;
                $this->mm($pattern)->save();
                # - Generate json response
                return $this->response([
                    $modal->execute('close'),
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
     * Generate glossaries form for pattern
     *
     * @param AmadeusDecoderPattern $_pattern
     *
     * @return \Qore\Form\FormManager|null
     */
    protected function getPatternGlossariesForm(AmadeusDecoderPattern $_pattern): ?FormManager
    {
        if (! $_pattern->groups || ! is_array($_pattern->groups) || ! count($_pattern->groups)) {
            return null;
        }

        /** @var FormManager */
        $fm = Qore::service(FormManager::class)('pattern-glossaries-form', Qore::url(
            $this->getRouteName('glossaries'),
            ['id' => $_pattern['id']]
        ));

        $glossaries = $this->mm('SM:AmadeusGlossary')->all();

        foreach ($_pattern->groups as $group => $value) {
            $fm->setField(new Select($group, [
                'label' => $group,
                'placeholder' => 'Словарь не назначен',
                'value' => $value,
                'options' => $glossaries->map(fn($_glossary) => [
                    'id' => $_glossary->id,
                    'label' => $_glossary->title,
                ])->toList(),
            ]));
        }

        $fm->setField(new Submit('submit', [ 'label' => 'Сохранить' ]));

        return $fm;
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
        # - Формируем уникальный суффикс для имени компонента интерфейса
        $testFilters = $this->model->getFilters(true)->firstMatch([
            'referencePath' => '{relation.path}' # Example: {relation.path} => @this.id
        ]);

        return $this->presentAs(ListComponent::class, [
            'actions' => $this->getListActions(),
            'suffix' => $testFilters['filters']['id'] ?? null,
            'sortable' => $this->getSortableOptions(),
            'columns' => [
                'id' => [
                    'label' => '#',
                    'class-header' => 'col-1 text-center',
                    'class-column' => 'col-1 text-center',
                ],
                'title' => [
                    'label' => 'Название',
                    'class-header' => 'col-2',
                    'class-column' => 'col-2',
                    'transform' => fn($_item) => $this->prepareColumTransform($_item)
                ],
                'name' => [
                    'label' => 'Коллекция',
                    'class-header' => 'col-1',
                    'class-column' => 'col-1',
                ],
                'regex' => [
                    'label' => 'Регулярное выражение',
                    'class-header' => 'col',
                    'class-column' => 'col',
                    'transform' => fn($_item) => htmlentities($_item['regex'] ?? ''),
                ],
            ],
        ])->build($_data);
    }

    /**
     * getListActions
     *
     */
    protected function getListActions()
    {
        return [
            'glossaries' => [
                'label' => 'Глоссарии',
                'icon' => 'far fa-spell-check',
                'actionUri' => function($_data) {
                    return Qore::service(UrlHelper::class)->generate(
                        $this->getRouteName('glossaries'),
                        ['id' => $_data['id']],
                    );
                },
            ],
            'groups' => [
                'label' => 'Назначить целевую группу',
                'icon' => 'fa fa-code-branch',
                'actionUri' => function($_data) {
                    return Qore::service(UrlHelper::class)->generate(
                        $this->getRouteName('define-target-group'),
                        ['id' => $_data['id']],
                    );
                },
            ],
            'update', 'delete',
        ];
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
    
    /**
     * prepareColumTransform
     * для активации функции древовидной структуры
     * @param  mixed $_item
     * @return array
     */
    private function prepareColumTransform($_item) : array
    {
        if ($this->isTreeStructure()) {
            return [
                'label' => $_item['title'],
                'actionUri' => Qore::service(UrlHelper::class)->generate(
                    $this->getRouteName('index'),
                    $this->model->getRouteResult()->getMatchedParams(),
                    array_merge($this->model->getSubjectFilters(), $this->getFilters($this, [
                        '__idparent' => $_item['id']
                    ]))
                ),
            ];
        }
        return ['label' => $_item['title']];
    }
    
    /**
     * detectParentGroup
     * Allow you to pick up the group from parrent groups 
     * @return ResultInterface
     */
    protected function defineTargetGroup() : ?ResultInterface
    {
        # - Init synpase structure
        $this->next->process($this->model);

        $request = $this->model->getRequest();
        $routeResult = $this->model->getRouteResult();
        $routeParams = $routeResult->getMatchedParams();

        $pattern = $this->mm('SM:AmadeusDecoderPattern')
            ->where(['@this.id' => $routeParams['id']])
            ->one();

        if (is_null($pattern) || $pattern->__idparent === '0') {
            return $this->response(['Error' => 'Pattern is empty or it hasn\'t a parent']);
        }

        $parentPattern = $this->mm('SM:AmadeusDecoderPattern')
            ->where(['@this.id' => $pattern->__idparent])
            ->one();

        $fm = $this->getPatternGroupsForm($pattern, $parentPattern);

        if (is_null($fm)) {
            return $this->response(['Error' => 'FormManager wasn\'t generated becouse of mistake']);
        }

        $ig = Qore::service(InterfaceGateway::class);
        $modal = $ig(Modal::class, sprintf('%s.%s', get_class($this), 'modal-update'))
            ->setTitle('Редактирование')
            ->component(Qore::service(QoreFront::class)->decorate($fm));

        if ($request->getMethod() === 'POST') {
            if ($fm->isValid()) {
                $data = $request->getParsedBody();
                $pattern->targetGroup = $data['target-group'];
                $this->mm($pattern)->save();
                
                # - Generate json response
                return $this->response([
                    $modal->execute('close'),
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
     * Generate groups form for pattern
     *
     * @param AmadeusDecoderPattern $_pattern
     *
     * @return \Qore\Form\FormManager|null
     */
    protected function getPatternGroupsForm(AmadeusDecoderPattern $_pattern,AmadeusDecoderPattern $_parentPattern): ?FormManager
    {
        if (! $_parentPattern->groups || ! is_array($_parentPattern->groups) || ! count($_parentPattern->groups)) {
            return null;
        }

        /** @var FormManager */
        $fm = Qore::service(FormManager::class)('pattern-glossaries-form', Qore::url(
            $this->getRouteName('define-target-group'),
            ['id' => $_pattern['id']]
        ));
        
        $options = [];
        foreach($_parentPattern->groups as $group => $value) 
        {
            $options []= [
                'id' => $group,
                'label' => $group
            ];
        }

        $fm->setField(new Select('target-group', [
            'label' => 'Список групп родителя',
            'placeholder' => $_pattern->targetGroup,
            'options' =>  $options
        ]));

        $fm->setField(new Submit('submit', [ 'label' => 'Сохранить' ]));
        return $fm;
    }
}
