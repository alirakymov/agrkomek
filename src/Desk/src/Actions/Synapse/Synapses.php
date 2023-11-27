<?php

namespace Qore\Desk\Actions\Synapse;

use Qore\InterfaceGateway\Component\Modal;
use Qore\InterfaceGateway\Component\Table;
use Qore\InterfaceGateway\InterfaceGateway;
use Qore\Qore;
use Qore\Front as QoreFront;
use Qore\Router\RouteCollector;
use Qore\Desk\Actions\BaseAction;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Mezzio\Helper\UrlHelper;
use Mezzio\Router\RouteResult;

/**
 * Class: Synapses
 *
 * @see BaseAction
 */
class Synapses extends BaseAction
{
    /**
     * synapses
     *
     * @var mixed
     */
    private $synapses = null;

    /**
     * Return routes array
     *
     * @return array
     */
    public function routes(RouteCollector $_router) : void
    {
        $_router->group('/synapse', null, function ($_router) {
            $_router->any('[/{id:[0-9]+}]', 'index');
            $_router->any('/create[/{id:[0-9]+}]', 'create');
            $_router->any('/update/{id:\d+}', 'update');
            $_router->any('/delete/{id:\d+}', 'delete');
        });
    }

    /**
     * run
     *
     */
    protected function run()
    {
        $routeResult = $this->request->getAttribute(RouteResult::class, null);

        switch (true) {
            case $routeResult->getMatchedRouteName() === $this->routeName('index'):
                return $this->runIndex();

            case $routeResult->getMatchedRouteName() === $this->routeName('create'):
                return $this->runCreate();

            case $routeResult->getMatchedRouteName() === $this->routeName('update'):
                return $this->runUpdate();

            case $routeResult->getMatchedRouteName() === $this->routeName('delete'):
                return $this->runDelete();
        }
    }

    /**
     * runIndex
     *
     */
    protected function runIndex()
    {
        $routeResult = $this->request->getAttribute(RouteResult::class, null);
        $routeParams = $routeResult->getMatchedParams();

        $mm = Qore::service(\Qore\ORM\ModelManager::class);

        $columns = [
            'id' => [
                'label' => '#',
                'model-path' => 'id',
                'class-header' => 'col-sm-1 text-center',
                'class-column' => 'col-sm-1 text-center',
            ],
            'name' => [
                'label' => 'Структура',
                'class-header' => 'col-sm-4',
                'transform' => function($_item) {
                    return [
                        'label' => $_item->name,
                        'actionUri' => Qore::service(UrlHelper::class)->generate($this->routeName('index'), ['id' => $_item->id]),
                    ];
                }
            ],
            'description' => [
                'label' => 'Описание',
                'class-header' => 'col-sm-5',
            ],
            'table-actions' => [
                'label' => 'Действия',
                'class-header' => 'col-sm-2 text-center',
                'class-column' => 'col-sm-2 text-center',
                'actions' => [
                    'structure' => [
                        'label' => 'Структура',
                        'icon' => 'fa fa-bars',
                        'actionUri' => function($_data) {
                            return Qore::service(UrlHelper::class)->generate($this->routeName(SynapseStructure::class, 'index'), ['synapse-id' => $_data->id]);
                        },
                    ],
                    'update' => [
                        'label' => 'Редактировать',
                        'icon' => 'fas fa-pen',
                        'actionUri' => function($_data) {
                            return Qore::service(UrlHelper::class)->generate($this->routeName('update'), ['id' => $_data->id]);
                        },
                    ],
                    'delete' => [
                        'label' => 'Удалить',
                        'icon' => 'fa fa-trash',
                        'actionUri' => function($_data) {
                            return Qore::service(UrlHelper::class)->generate($this->routeName('delete'), ['id' => $_data->id]);
                        },
                        'confirm' => function($_data) {
                            return [
                                'title' => 'Удаление синапса',
                                'message' => sprintf('Вы действительно хотите удалить синапс "%s"?', $_data->name)
                            ];
                        },
                    ],
                ],
            ]
        ];

        $componentActions = [
            'create' => [
                'icon' => 'fa fa-plus',
                'actionUri' => Qore::service(UrlHelper::class)->generate($this->routeName('create'), ['id' => $routeParams['id'] ?? 0])
            ],
            'inspect' => [
                'icon' => 'fa fa-cogs',
                'actionUri' => Qore::service(UrlHelper::class)->generate($this->routeName(\Qore\Desk\Actions\Inspect::class, 'index'))
            ]
        ];

        $ig = Qore::service(InterfaceGateway::class);

        $component = $ig(Table::class, 'synapses')
            ->setActions($componentActions)
            ->setTitle('Структура синапсов')
            ->setTableOptions([
                'url' => Qore::service(UrlHelper::class)->generate($this->routeName('index'))
            ])->setTableData($columns, $this->getData($routeParams['id'] ?? 0))
            ->setBreadcrumbs($this->getBreadcrumbs($routeParams['id'] ?? 0));

        if ($this->request->isXmlHttpRequest()) {
            return QoreFront\ResponseGenerator::get($component);
        } else {
            $layout = $this->getFrontProtocol()
                ->component($component->inBlock(true));
            return new HtmlResponse($this->template->render('app::main', [
                'title' => 'Qore.CRM',
                'frontProtocol' => $layout->compose(),
            ]));
        }
    }

    /**
     * getBreadcrumbs
     *
     * @param mixed $_iParent
     */
    private function getBreadcrumbs($_iParent)
    {
        $mm = Qore::service(\Qore\ORM\ModelManager::class);
        if ((int)$_iParent === 0) {
            return [
                [
                    'label' => 'Корень',
                    'actionUri' => Qore::service(UrlHelper::class)->generate($this->routeName('index'), ['id' => $_iParent])
                ]
            ];
        } else {

            $result = $mm('QSynapse:Synapses')
                ->where(function($_where) use ($_iParent) {
                    $_where(['@this.id' => $_iParent]);
                })->one();

            return array_merge(
                $this->getBreadcrumbs($result['iParent']),
                [
                    [
                        'label' => $result['name'],
                        'actionUri' => Qore::service(UrlHelper::class)->generate($this->routeName('index'), ['id' => $result['id']]),
                    ]
                ]
            );
        }
    }

    /**
     * getUsers
     *
     */
    private function getData($_iParent = 0)
    {
        $mm = Qore::service(\Qore\ORM\ModelManager::class);
        return $mm('QSynapse:Synapses')
            ->where(function($_where) use ($_iParent){
                $_where(['@this.iParent' => $_iParent]);
            })
            ->all();
    }

    /**
     * runCreate
     *
     */
    protected function runCreate()
    {
        $routeResult = $this->request->getAttribute(RouteResult::class, null);
        $routeParams = $routeResult->getMatchedParams();

        $fm = $this->getForm();

        $ig = Qore::service(InterfaceGateway::class);
        $modal = $ig(Modal::class, 'create-synapse');
        if ($this->request->getMethod() === 'POST') {

            $fm->setData($this->request->getParsedBody());

            if ($fm->isValid()) {
                # - Save form data through Qore\ORM
                ($mm = Qore::service(\Qore\ORM\ModelManager::class))(
                    $mm('QSynapse:Synapses', $fm->getData())
                )->save();

                return QoreFront\ResponseGenerator::get(
                    # - Command: modal close
                    $ig(Modal::class, 'create-synapse')
                        ->run('close'),
                    # - Command: reload table
                    $ig(Table::class, 'synapses')
                        ->run('reload')
                );

            } else {
                # - Генерируем ответ
                return QoreFront\ResponseGenerator::get(
                    # - Command: update form
                    Qore::service(\Qore\Form\Decorator\QoreFront::class)->decorate($fm)
                );
            }

        } else {

            $fm->setData([ 'iParent' => $routeParams['id'] ?? 0 ]);

            return QoreFront\ResponseGenerator::get(
                $this->getFrontProtocol()->component(
                    $modal->setTitle('Создание нового синапса')
                        ->component(Qore::service(\Qore\Form\Decorator\QoreFront::class)->decorate($fm))
                        ->run('open')
                )
            );
        }
    }

    /**
     * runCreate
     *
     */
    protected function runUpdate()
    {
        $routeResult = $this->request->getAttribute(RouteResult::class, null);
        $routeParams = $routeResult->getMatchedParams();

        $fm = $this->getForm();

        $ig = Qore::service(InterfaceGateway::class);
        $modal = $ig(Modal::class, 'update-synapse');

        $mm = Qore::service(\Qore\ORM\ModelManager::class);
        $result = $mm('QSynapse:Synapses')
            ->where(function($_where) use ($routeParams) {
                $_where(['@this.id' => $routeParams['id']]);
            })
            ->one();

        if ($this->request->getMethod() === 'POST') {

            $fm->setData($this->request->getParsedBody());

            if ($fm->isValid()) {
                $data = $fm->getData();
                # - Save form data through Qore\ORM
                $mm($result->combine($data))->save();

                return QoreFront\ResponseGenerator::get(
                    # - Command: modal close
                    $ig(Modal::class, 'update-synapse')
                        ->run('close'),
                    # - Command: reload table
                    $ig(Table::class, 'synapses')
                        ->run('reload')
                );

            } else {
                # - Generate response for form
                return QoreFront\ResponseGenerator::get(
                    # - Command: update form
                    Qore::service(\Qore\Form\Decorator\QoreFront::class)->decorate($fm)
                );
            }

        } else {

            $fm->setData([ 'iParent' => $routeParams['id'] ?? '0' ]);
            $result['iParent'] = (string)$result['iParent'];
            $fm->setData($result);

            return QoreFront\ResponseGenerator::get(
                $this->getFrontProtocol()->component(
                    $modal->setTitle('Редактирование синапса')
                        ->component(Qore::service(\Qore\Form\Decorator\QoreFront::class)->decorate($fm))
                        ->run('open')
                )
            );
        }
    }

    /**
     * getForm
     *
     */
    protected function getForm()
    {
        $routeResult = $this->request->getAttribute(RouteResult::class);
        $routeParams = $routeResult->getMatchedParams();
        $isNew = $routeResult->getMatchedRouteName() === $this->routeName('create');

        $formFields = [
            'iParent' => [
                'type' => \Qore\Form\Field\TreeSelect::class,
                'label'=> 'Родительский синапс',
                'validators' => [
                    [
                        'type' => \Qore\Form\Validator\Required::class,
                        'message' => 'Выберите синапс',
                        'break' => false,
                    ],
                ],
                'options' => $this->getSynapses($isNew ? null : $routeParams['id']),
                'additional' => []
            ],
            'name' => [
                'type' => \Qore\Form\Field\Text::class,
                'label'=> 'Название*',
                'placeholder' => 'Введите название синапса',
                'info' => 'название должно быть уникальным и состоять только из латинских символов нижнего/верхнего регистра, а также цифр',
                'validators' => [
                    [
                        'type' => \Qore\Form\Validator\Required::class,
                        'message' => 'Поле название обязательно.',
                        'break' => false,
                    ],
                    [
                        'type' => \Qore\Form\Validator\Regex::class,
                        'options' => [
                            'pattern' => '/^[A-z0-9]+$/'
                        ],
                        'message' => 'Неправильно заполнено поле',
                        'break' => true,
                    ],
                ],
            ],
            'tree' => [
                'type' => \Qore\Form\Field\Switcher::class,
                'label'=> 'Назначить синапсу древовидную структуру',
                'placeholder' => '',
                'info' => ''
            ],
            'description' => [
                'type' => \Qore\Form\Field\Textarea::class,
                'label'=> 'Описание',
                'placeholder' => 'Описание',
                'validators' => [
                ]
            ],
            'submit' => [
                'type' => \Qore\Form\Field\Submit::class,
                'label' => 'Создать',
            ],
        ];

        return Qore::service(\Qore\Form\FormManager::class)(
            # - Form name
            'synapse-form',
            # - Form action
            Qore::service(UrlHelper::class)->generate(
                $routeResult->getMatchedRouteName(),
                $routeResult->getMatchedParams()
            ),
            # - Form fields
            $formFields
        );
    }

    /**
     * getSynapses
     *
     * @param int $_iDisabled
     */
    private function getSynapses($_iDisabled = null)
    {
        if (! $this->synapses) {
            $this->loadSynapses();
        }

        return [
            [
                'id' => '0',
                'label' => 'Корень',
                'children' => $this->compareSynapses(0, $_iDisabled),
                'isDefaultExpanded' => true,
            ]
        ];
    }

    /**
     * loadSynapses
     *
     */
    private function loadSynapses()
    {
        $mm = Qore::service(\Qore\ORM\ModelManager::class);
        $this->synapses = $mm('QSynapse:Synapses')
            ->all()
            ->map(function($synapse){
                return [
                    'id' => $synapse['id'],
                    'iParent' => $synapse['iParent'],
                    'name' => $synapse['name']
                ];
            })
            ->toArray(false);
    }

    /**
     * compareSynapses
     *
     * @param mixed $_iParent
     * @param mixed $_iDisabled
     */
    private function compareSynapses($_iParent, $_iDisabled = null)
    {
        $result = [];

        reset($this->synapses);

        foreach ($this->synapses as $synapse) {

            if ((int)$synapse['iParent'] === (int)$_iParent) {
                $result[] = [
                    'id' => $synapse['id'],
                    'label' => $synapse['name'],
                    'children' => $this->compareSynapses($synapse['id'], $_iDisabled),
                    'isDisabled' => ! is_null($_iDisabled) && (int)$synapse['id'] === (int)$_iDisabled,
                ];
            }
        }

        return $result;
    }

    /**
     * runDelete
     *
     */
    protected function runDelete()
    {
        Qore::service('debug')->message($this->routeName('delete'));

        $routeResult = $this->request->getAttribute(RouteResult::class, null);
        $routeParams = $routeResult->getMatchedParams();

        ($mm = Qore::service(\Qore\ORM\ModelManager::class))(
            $mm('QSynapse:Synapses', ['id' => $routeParams['id']])
        )->delete();

        $ig = Qore::service(InterfaceGateway::class);

        return QoreFront\ResponseGenerator::get(
            $ig('synapses')->run('reload')
        );
    }

}
