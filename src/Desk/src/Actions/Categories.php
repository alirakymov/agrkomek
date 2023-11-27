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

class Categories extends BaseAction
{
    /**
     * accessPrivilege
     *
     * @var int
     */
    protected $accessPrivilege = 100;

    /**
     * categories
     *
     * @var mixed
     */
    protected $categories = [];

    /**
     * Return routes array
     *
     * @return array
     */
    public function routes(RouteCollector $_router) : void
    {
        $_router->group('/categories', null, function ($_router) {
            $_router->any('[/{id:\d+}]', 'index');
            $_router->any('/create[/{id:\d+}]', 'create');
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
        $routeResult = $this->request->getAttribute(\Mezzio\Router\RouteResult::class);

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

        $columns = [
            'id' => [
                'label' => '#',
                'model-path' => 'id',
                'class-header' => 'col-sm-1 text-center',
                'class-column' => 'col-sm-1 text-center',
            ],
            'name' => [
                'label' => 'Категория',
                'class-header' => 'col-sm-9',
                'class-column' => 'col-sm-9',
                'transform' => function($_item) {
                    return [
                        'label' => $_item->name,
                        'actionUri' => Qore::service(UrlHelper::class)->generate($this->routeName('index'), ['id' => $_item->id]),
                    ];
                }
            ],
            'table-actions' => [
                'label' => 'Действия',
                'class-header' => 'col-sm-2 text-center',
                'class-column' => 'col-sm-2 text-center',
                'actions' => [
                    'properties' => [
                        'label' => 'Свойства товаров',
                        'icon' => 'fa fa-bars',
                        'actionUri' => function($_item) {
                            return Qore::service(UrlHelper::class)->generate($this->routeName(CategoryProperties::class, 'index'), ['id' => $_item->id]);
                        },
                    ],
                    'update' => [
                        'label' => 'Редактировать',
                        'icon' => 'fa fa-pencil',
                        'actionUri' => function($_item) {
                            return Qore::service(UrlHelper::class)->generate($this->routeName('update'), ['id' => $_item->id]);
                        },
                    ],
                    'delete' => [
                        'label' => 'Удалить',
                        'icon' => 'fa fa-trash',
                        'actionUri' => function($_item) {
                            return Qore::service(UrlHelper::class)->generate($this->routeName('delete'), ['id' => $_item->id]);
                        },
                        'confirm' => function($_item) {
                            return [
                                'title' => 'Удаление категории',
                                'message' => sprintf('Вы действительно хотите удалить категорию "%s"?', $_item->name),
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
            ]
        ];

        $component = QoreFront\Protocol\Component\QCTable::get('categories')
            ->setActions($componentActions)
            ->setTableOptions([
                'url' => Qore::service(UrlHelper::class)->generate($this->routeName('index'), ['id' => $routeParams['id'] ?? 0])
            ])
            ->setTitle('Категории')
            ->setTableData($columns, $this->getCategories($routeParams['id'] ?? 0))
            ->setBreadcrumbs($this->getBreadcrumbs($routeParams['id'] ?? 0));

        if ($this->request->isXmlHttpRequest()) {

            return QoreFront\ResponseGenerator::get($component);

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
     * getCategories
     *
     */
    private function getCategories($_iParent)
    {
        $mm = Qore::service(\Qore\ORM\ModelManager::class);
        return $mm('Samsung:Categories')
            ->where(function($_where) use ($_iParent) {
                $_where(['@this.iParent' => $_iParent]);
            })
            ->all();
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
            $result = $mm('Samsung:Categories')
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

                # - Save form data through Qore\ORM
                ($mm = Qore::service(\Qore\ORM\ModelManager::class))(
                    $mm('Samsung:Categories', $fm->getData())
                )->save();

                return QoreFront\ResponseGenerator::get(
                    # - Command: modal close
                    QoreFront\Protocol\Component\QCModal::get('create-category')
                        ->run('close'),
                    # - Command: reload table
                    QoreFront\Protocol\Component\QCTable::get('categories')
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
                'iParent' => $routeParams['id'] ?? 0,
            ]);

            return QoreFront\ResponseGenerator::get(
                $this->getFrontProtocol()
                    ->component(
                        QoreFront\Protocol\Component\QCModal::get('create-category')
                            ->setTitle('Добавление категории')
                            ->component(Qore::service(\Qore\Form\Decorator\QoreFront::class)->decorate($fm))
                    ),
                QoreFront\Protocol\Component\QCModal::get('create-category')
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
                    $mm('Samsung:Categories', array_merge($data, ['id' => $routeParams['id']]))
                )->save();

                return QoreFront\ResponseGenerator::get(
                    # - Command: modal close
                    QoreFront\Protocol\Component\QCModal::get('update-category')
                        ->run('close'),
                    # - Command: reload table
                    QoreFront\Protocol\Component\QCTable::get('categories')
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
            $result = $mm('Samsung:Categories')
                ->where(function($_where) use ($routeParams) {
                    $_where(['@this.id' => $routeParams['id']]);
                })
                ->one();

            $fm->setData($result);

            return QoreFront\ResponseGenerator::get(
                $this->getFrontProtocol()
                    ->component(
                        QoreFront\Protocol\Component\QCModal::get('update-category')
                            ->setTitle('Редактирование категории')
                            ->component(Qore::service(\Qore\Form\Decorator\QoreFront::class)->decorate($fm))
                    ),
                QoreFront\Protocol\Component\QCModal::get('update-category')
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
                'label'=> 'Родительская категория',
                'validators' => [
                    [
                        'type' => \Qore\Form\Validator\Required::class,
                        'message' => 'Выберите категорию',
                        'break' => false,
                    ],
                ],
                'options' => $this->getAllCategories($isNew ? null : $routeParams['id']),
                'additional' => []
            ],
            'name' => [
                'type' => \Qore\Form\Field\Text::class,
                'label'=> 'Категория',
                'placeholder' => 'Введите название категории',
                'validators' => [
                    [
                        'type' => \Qore\Form\Validator\Required::class,
                        'message' => 'Поле email обязательно.',
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
            'new-category',
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
    private function loadCategories()
    {
        $mm = Qore::service(\Qore\ORM\ModelManager::class);
        $this->categories = $mm('Samsung:Categories')
            ->all()
            ->toArray();
    }

    /**
     * getAllCategories
     *
     * @param int $_iDisabled
     */
    private function getAllCategories($_iDisabled = null)
    {
        if (! $this->categories) {
            $this->loadCategories();
        }

        return [
            [
                'id' => 0,
                'label' => 'Корень',
                'children' => $this->compareCategories(0, $_iDisabled),
                'isDefaultExpanded' => true,
            ]
        ];
    }

    /**
     * compareCategories
     *
     * @param mixed $_iParent
     * @param mixed $_iDisabled
     */
    private function compareCategories($_iParent, $_iDisabled = null)
    {
        $result = [];

        foreach ($this->categories as $category) {

            if ((int)$category['iParent'] === (int)$_iParent) {
                $result[] = [
                    'id' => $category['id'],
                    'label' => $category['name'],
                    'children' => $this->compareCategories($category['id'], $_iDisabled),
                    'isDisabled' => ! is_null($_iDisabled) && (int)$category['id'] === (int)$_iDisabled,
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
        $routeResult = $this->request->getAttribute(RouteResult::class, null);
        $routeParams = $routeResult->getMatchedParams();

        ($mm = Qore::service(\Qore\ORM\ModelManager::class))(
            $mm('Samsung:Categories', ['id' => $routeParams['id']])
        )->delete();

        return QoreFront\ResponseGenerator::get(
            QoreFront\Protocol\Component\QCTable::get('categories')
                ->run('reload')
        );
    }

}
