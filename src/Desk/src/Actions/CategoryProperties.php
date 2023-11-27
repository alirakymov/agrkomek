<?php

declare(strict_types=1);

namespace Qore\Desk\Actions;


use Qore\Qore;
use Qore\Front as QoreFront;
use Qore\Router\RouteCollector;
use Qore\Db\TableGateway\TableGateway;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Mezzio\Helper\UrlHelper;
use Mezzio\Router\RouteResult;
use PAMI\Client\Impl\ClientImpl as PamiClient;
use PAMI\Message\Action\OriginateAction;

class CategoryProperties extends BaseAction
{
    /**
     * accessPrivilege
     *
     * @var int
     */
    protected $accessPrivilege = 100;

    /**
     * properties
     *
     * @var mixed
     */
    protected $properties = [];

    /**
     * Return routes array
     *
     * @return array
     */
    public function routes(RouteCollector $_router) : void
    {
        $_router->group('/category-params/{id:\d+}', null, function ($_router) {
            $_router->any('[/{id-parent:\d+}]', 'index');
            $_router->any('/reload[/{id-parent:\d+}]', 'reload');
            $_router->any('/create[/{id-parent:\d+}]', 'create');
            $_router->any('/update/{id-property:\d+}', 'update');
            $_router->any('/delete/{id-property:\d+}', 'delete');
        });
    }

    /**
     * run
     *
     */
    protected function run()
    {
        $routeResult = $this->request->getAttribute(\Mezzio\Router\RouteResult::class);

        switch (true) {
            case $routeResult->getMatchedRouteName() === $this->routeName('index'):
                return $this->runIndex();

            case $routeResult->getMatchedRouteName() === $this->routeName('reload'):
                return $this->runReload();

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
        $component = $this->getPropertyComponent();

        if ($this->request->isXmlHttpRequest()) {
            return QoreFront\ResponseGenerator::get(
                $this->getFrontProtocol()
                      ->component($component->inBlock(true))
            );
        } else {
            $frontProtocol = $this->getFrontProtocol()
                ->component($component->inBlock(true));
            return new HtmlResponse($this->template->render('app::main', [
                'title' => 'Qore.CRM',
                'frontProtocol' => $frontProtocol->asArray(),
            ]));
        }
    }

    /**
     * runReload
     *
     */
    protected function runReload()
    {
        $component = $this->getPropertyComponent();
        return QoreFront\ResponseGenerator::get($component);
    }

    protected function runReorder()
    {
        $params = $this->request->getParsedBody();
        \dd($params);
    }

    /**
     * getPropertyComponent
     *
     */
    private function getPropertyComponent()
    {
        $routeResult = $this->request->getAttribute(RouteResult::class, null);
        $routeParams = $routeResult->getMatchedParams();

        $columns = [
            'id' => [
                'label' => '#',
                'model-path' => 'id',
                'class-header' => 'col-sm-1 text-center',
                'class-column' => 'col-sm-1 text-center',
            ],
            'label' => [
                'label' => 'Свойство',
                'class-header' => 'col-sm-10',
                'transform' => function($_item) {
                    if ((int)$_item['type'] !== 1) {
                        return [
                            'label' => $_item->label,
                            'actionUri' => Qore::service(UrlHelper::class)->generate($this->routeName('reload'), [
                                'id' => $_item->category->id,
                                'id-parent' => $_item->id,
                            ]),
                        ];
                    } else {
                        return $_item->label;
                    }
                }
            ],
            'table-actions' => [
                'label' => 'Действия',
                'class-header' => 'col-sm-1 text-center',
                'class-column' => 'col-sm-1 text-center',
                'actions' => [
                    'update' => [
                        'label' => 'Редактировать',
                        'icon' => 'fa fa-pencil',
                        'actionUri' => function($_item) {
                            return Qore::service(UrlHelper::class)->generate($this->routeName('update'), [
                                'id' => $_item->category->id,
                                'id-property' => $_item->id,
                            ]);
                        },
                    ],
                    'delete' => [
                        'label' => 'Удалить',
                        'icon' => 'fa fa-trash',
                        'actionUri' => function($_item) {
                            return Qore::service(UrlHelper::class)->generate($this->routeName('delete'), [
                                'id' => $_item->category->id,
                                'id-property' => $_item->id,
                            ]);
                        },
                        'confirm' => function($_item) {
                            return [
                                'title' => 'Удаление свойства',
                                'message' => sprintf('Вы действительно хотите удалить свойство "%s"?', $_item->label),
                            ];
                        },
                    ],
                ],
            ]
        ];

        $componentActions = [
            'create' => [
                'icon' => 'fa fa-plus',
                'actionUri' => Qore::service(UrlHelper::class)->generate($this->routeName('create'), [
                    'id' => $routeParams['id'],
                    'id-parent' => $routeParams['id-parent'] ?? 0
                ])
            ],
            'destroy' => [
                'icon' => 'fa fa-times',
                'actionUri' => 'destroy'
            ],
        ];

        $mm = Qore::service(\Qore\ORM\ModelManager::class);

        $category = $mm('Samsung:Categories')
            ->where(function($_where) use ($routeParams) {
                $_where(['@this.id' => $routeParams['id']]);
            })
            ->one();

        $properties = $mm('Samsung:CategoryProperties')
            ->with('category')
            ->where(function($_where) use ($routeParams) {
                $_where(['@this.iCategory' => $routeParams['id']])
                    ->and(['@this.iParent' => $routeParams['id-parent'] ?? 0]);
            })
            ->all();

        return QoreFront\Protocol\Component\QCTable::get('category-properties')
            ->setActions($componentActions, true)
            ->setTableOptions([
                'url' => Qore::service(UrlHelper::class)->generate($this->routeName('reload'), $routeParams)
            ])
            ->setTitle(sprintf('Свойства категории "%s"', $category->name))
            ->setTableData($columns, $properties)
            ->setBreadcrumbs($this->getBreadcrumbs($routeParams['id-parent'] ?? 0, $routeParams['id']));
    }

    /**
     * getBreadcrumbs
     *
     * @param mixed $_iParent
     */
    private function getBreadcrumbs($_iParent, $_iCategory)
    {
        $mm = Qore::service(\Qore\ORM\ModelManager::class);
        if ((int)$_iParent === 0) {
            return [
                [
                    'label' => 'Корень',
                    'actionUri' => Qore::service(UrlHelper::class)->generate($this->routeName('reload'), [
                        'id' => $_iCategory,
                        'id-parent' => $_iParent,
                    ])
                ]
            ];
        } else {

            $result = $mm('Samsung:CategoryProperties')
                ->where(function($_where) use ($_iParent) {
                    $_where(['@this.id' => $_iParent]);
                })->one();

            return array_merge(
                $this->getBreadcrumbs($result['iParent'], $_iCategory),
                [
                    [
                        'label' => $result['label'],
                        'actionUri' => Qore::service(UrlHelper::class)->generate($this->routeName('reload'), [
                            'id' => $_iCategory,
                            'id-parent' => $result['id'],
                        ]),
                    ]
                ]
            );
        }
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

        if ($this->request->getMethod() === 'POST') {

            $fm->setData($this->request->getParsedBody());

            if ($fm->isValid()) {

                $data = $fm->getData();
                $data = array_merge($data, [
                    'iCategory' => $routeParams['id'],
                    'type' => (int)$data['iProperty'] === 0 ? 0 : 1,
                ]);
                # - Save form data through Qore\ORM
                ($mm = Qore::service(\Qore\ORM\ModelManager::class))(
                    $mm('Samsung:CategoryProperties', $data)
                )->save();

                return QoreFront\ResponseGenerator::get(
                    # - Command: modal close
                    QoreFront\Protocol\Component\QCModal::get('create-category-property')
                        ->run('close'),
                    # - Command: reload table
                    QoreFront\Protocol\Component\QCTable::get('category-properties')
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

            $fm->setData([
                'iParent' => $routeParams['id-parent'] ?? 0,
                'iProperty' => 0,
            ]);

            return QoreFront\ResponseGenerator::get(
                $this->getFrontProtocol()
                    ->component(
                        QoreFront\Protocol\Component\QCModal::get('create-category-property')
                            ->setTitle('Добавление свойства категории')
                            ->component(Qore::service(\Qore\Form\Decorator\QoreFront::class)->decorate($fm))
                    ),
                QoreFront\Protocol\Component\QCModal::get('create-category-property')
                    ->run('open')
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

        if ($this->request->getMethod() === 'POST') {

            $fm->setData($this->request->getParsedBody());

            if ($fm->isValid()) {

                $data = $fm->getData();

                # - Save form data through Qore\ORM
                ($mm = Qore::service(\Qore\ORM\ModelManager::class))(
                    $mm('Samsung:CategoryProperties', array_merge($data, ['id' => $routeParams['id-property']]))
                )->save();

                return QoreFront\ResponseGenerator::get(
                    # - Command: modal close
                    QoreFront\Protocol\Component\QCModal::get('update-category-property')
                        ->run('close'),
                    # - Command: reload table
                    QoreFront\Protocol\Component\QCTable::get('category-properties')
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

            $mm = Qore::service(\Qore\ORM\ModelManager::class);
            $result = $mm('Samsung:CategoryProperties')
                ->where(function($_where) use ($routeParams) {
                    $_where(['@this.id' => $routeParams['id-property']]);
                })
                ->one();

            $fm->setData($result);

            return QoreFront\ResponseGenerator::get(
                $this->getFrontProtocol()
                    ->component(
                        QoreFront\Protocol\Component\QCModal::get('update-category-property')
                            ->setTitle('Редактирование свойства категории')
                            ->component(Qore::service(\Qore\Form\Decorator\QoreFront::class)->decorate($fm))
                    ),
                QoreFront\Protocol\Component\QCModal::get('update-category-property')
                    ->run('open')
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
                'label'=> 'Группа',
                'validators' => [
                    [
                        'type' => \Qore\Form\Validator\Required::class,
                        'message' => 'Выберите группу',
                        'break' => false,
                    ],
                ],
                'options' => $this->getPropertiesGroups($routeParams['id'], $isNew ? null : $routeParams['id']),
                'additional' => []
            ],
            'iProperty' => [
                'type' => \Qore\Form\Field\TreeSelect::class,
                'label'=> 'Свойство',
                'validators' => [
                    [
                        'type' => \Qore\Form\Validator\Required::class,
                        'message' => 'Выберите группу',
                        'break' => false,
                    ],
                ],
                'options' => $this->getProperties(),
                'additional' => []
            ],
            'label' => [
                'type' => \Qore\Form\Field\Text::class,
                'label'=> 'Свойство',
                'placeholder' => 'Введите название свойства',
                'validators' => [
                    [
                        'type' => \Qore\Form\Validator\Required::class,
                        'message' => 'Задайте название добваляемому свойству.',
                        'break' => false,
                    ],
                ]
            ],
            'position' => [
                'type' => \Qore\Form\Field\Text::class,
                'label'=> 'Порядковый индекс',
                'placeholder' => 'Введите порядковый индекс',
                'validators' => [
                    [
                        'type' => \Qore\Form\Validator\Required::class,
                        'message' => 'Задайте порядковый индекс свойству.',
                        'break' => false,
                    ],
                ]
            ],
            'submit' => [
                'type' => \Qore\Form\Field\Submit::class,
                'label' => isset($routeParams['id']) ? 'Сохранить' : 'Добавить',
            ],
        ];

        return Qore::service(\Qore\Form\FormManager::class)(
            # - Form name
            'new-property',
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
     * getAllCategories
     *
     */
    private function loadProperties($_iCategory)
    {
        $mm = Qore::service(\Qore\ORM\ModelManager::class);
        $this->properties = $mm('Samsung:CategoryProperties')
             ->where(function($_where) use ($_iCategory) {
                 $_where(['@this.iCategory' => $_iCategory])
                     ->and(['@this.type' => 0]);
             })
            ->all();
    }

    /**
     * getAllCategories
     *
     * @param int $_iDisabled
     */
    private function getPropertiesGroups($_iCategory, $_iDisabled = null)
    {
        if (! $this->properties) {
            $this->loadProperties($_iCategory);
        }

        return [
            [
                'id' => 0,
                'label' => 'Корень',
                'children' => $this->compareProperties(0, $_iDisabled),
                'isDefaultExpanded' => true,
            ]
        ];
    }

    /**
     * compareProperties
     *
     * @param mixed $_iParent
     * @param mixed $_iDisabled
     */
    private function compareProperties($_iParent, $_iDisabled = null)
    {
        $result = [];

        foreach ($this->properties as $property) {

            if ((int)$property['iParent'] === (int)$_iParent) {
                $result[] = [
                    'id' => $property['id'],
                    'label' => $property['label'],
                    'children' => $this->compareProperties($property['id'], $_iDisabled),
                    'isDisabled' => ! is_null($_iDisabled) && (int)$property['id'] === (int)$_iDisabled,
                ];
            }
        }

        return $result;
    }

    /**
     * getProperties
     *
     */
    private function getProperties()
    {
        $mm = Qore::service(\Qore\ORM\ModelManager::class);

        $properties = $mm('Samsung:Properties')
            ->all();

        $return = [
            [ 'id' => 0, 'label' => 'Исп. как группу', ]
        ];

        foreach ($properties as $property) {
            $return[] = [
                'id' => $property['id'],
                'label' => $property['name']
            ];
        }

        return $return;
    }

    /**
     * runDelete
     *
     */
    protected function runDelete()
    {
        $routeResult = $this->request->getAttribute(RouteResult::class, null);
        $routeParams = $routeResult->getMatchedParams();

        Qore::service('debug')->message($routeParams);

        ($mm = Qore::service(\Qore\ORM\ModelManager::class))(
            $mm('Samsung:CategoryProperties', ['id' => $routeParams['id-property']])
        )->delete();

        return QoreFront\ResponseGenerator::get(
            QoreFront\Protocol\Component\QCTable::get('category-properties')
                ->run('reload')
        );
    }

}
