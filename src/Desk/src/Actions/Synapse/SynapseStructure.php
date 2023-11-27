<?php

namespace Qore\Desk\Actions\Synapse;

use Qore\InterfaceGateway\Component\Modal;
use Qore\InterfaceGateway\Component\Table;
use Qore\InterfaceGateway\Component\Tabs\Tabs;
use Qore\InterfaceGateway\InterfaceGateway;
use Qore\Qore;
use Qore\Front as QoreFront;
use Qore\Router\RouteCollector;
use Qore\SynapseManager\Structure\Entity;
use Qore\Desk\Actions\BaseAction;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Mezzio\Helper\UrlHelper;
use Mezzio\Router\RouteResult;

/**
 * Class: SynapseStructure
 *
 * @see BaseAction
 */
class SynapseStructure extends BaseAction
{
    /**
     * Return routes array
     *
     * @return array
     */
    public function routes(RouteCollector $_router) : void
    {
        $_router->group('/synapse-structure', null, function($_router) {
            $_router->any('/{synapse-id:[0-9]+}', 'index');
            $_router->group('/attributes', 'attributes', function($_router) {
                $_router->any('/reload/{synapse-id:[0-9]+}', 'reload');
                $_router->any('/create/{synapse-id:[0-9]+}', 'create');
                $_router->any('/update/{id:\d+}', 'update');
                $_router->any('/delete/{id:\d+}', 'delete');
            });
            $_router->group('/relations', 'relations', function($_router) {
                $_router->any('/reload/{synapse-id:[0-9]+}', 'reload');
                $_router->any('/create/{synapse-id:[0-9]+}', 'create');
                $_router->any('/update/{id:\d+}', 'update');
                $_router->any('/delete/{id:\d+}', 'delete');
            });
            $_router->group('/services', 'services', function($_router) {
                $_router->any('/reload/{synapse-id:[0-9]+}', 'reload');
                $_router->any('/create/{synapse-id:[0-9]+}', 'create');
                $_router->any('/update/{id:\d+}', 'update');
                $_router->any('/delete/{id:\d+}', 'delete');
            });
        });
    }

    /**
     * run
     *
     */
    protected function run()
    {
        $routeResult = $this->request->getAttribute(RouteResult::class, null);

        $methodName = 'run' . Qore::collection($this->splitRouteName($routeResult->getMatchedRouteName()))
            ->map(function($_value){
                return ucfirst($_value);
            })
            ->implode();

        return call_user_func([$this, $methodName]);
    }

    /**
     * runIndex
     *
     */
    protected function runIndex($_reload = false)
    {
        $routeResult = $this->request->getAttribute(RouteResult::class, null);
        $routeParams = $routeResult->getMatchedParams();

        if (! $synapse = $this->getSynapse($routeParams['synapse-id'])) {
            return new JsonResponse([]);
        }

        $ig = Qore::service(InterfaceGateway::class);

        $tabsComponent = $ig(Tabs::class, 'synapse-structure')
            ->setTitle(sprintf('Структура синапса "%s"', $synapse->name))
            ->tab('sevices', function($_tab) use ($synapse) {
                $_tab->setLabel('Сервисы')->component($this->getServicesComponent($synapse->id));
            })->tab('relations', function($_tab) use ($synapse) {
                $_tab->setLabel('Связи')->component($this->getRelationsComponent($synapse->id));
            })->tab('attributes', function($_tab) use ($synapse) {
                $_tab->setLabel('Атрибуты')->component($this->getAttributesComponent($synapse->id));
            })->indents(false);

        $frontProtocol = $this->getFrontProtocol()
            ->component($tabsComponent->inBlock());

        if ($this->request->isXmlHttpRequest()) {
            return QoreFront\ResponseGenerator::get($frontProtocol);
        } else {
            return new HtmlResponse($this->template->render('app::main', [
                'title' => 'Qore.CRM',
                'frontProtocol' => $frontProtocol->compose(),
            ]));
        }
    }

    /**
     * getSyanpse
     *
     * @param mixed $_id
     */
    private function getSynapse($_id)
    {
        $mm = Qore::service(\Qore\ORM\ModelManager::class);
        return $mm('QSynapse:Synapses')
            ->where(function($_where) use ($_id){
                $_where(['@this.id' => $_id]);
            })
            ->one();
    }

    /**
     * runAttributesReload
     *
     */
    private function runAttributesReload()
    {
        $routeResult = $this->request->getAttribute(RouteResult::class, null);
        $routeParams = $routeResult->getMatchedParams();
        return QoreFront\ResponseGenerator::get($this->getAttributesComponent($routeParams['synapse-id']));
    }

    /**
     * runAttributesCreate
     *
     */
    private function runAttributesCreate()
    {
        $routeResult = $this->request->getAttribute(RouteResult::class, null);
        $routeParams = $routeResult->getMatchedParams();

        if (! isset($routeParams['synapse-id'])) {
            return JsonResponse([]);
        }

        $fm = $this->getAttributesForm();
        $ig = Qore::service(InterfaceGateway::class);
        $modal = $ig(Modal::class, 'create-synapse-attribute');

        if ($this->request->getMethod() === 'POST') {

            $fm->setData($this->request->getParsedBody());

            if ($fm->isValid()) {
                # - Save form data through Qore\ORM
                ($mm = Qore::service(\Qore\ORM\ModelManager::class))(
                    $mm('QSynapse:SynapseAttributes', array_merge($fm->getData(), ['iSynapse' => $routeParams['synapse-id']]))
                )->save();

                return QoreFront\ResponseGenerator::get(
                    # - Command: modal close
                    $modal->run('close'),
                    # - Command: reload table
                    $ig('synapse-attributes')->run('reload')
                );

            } else {
                # - Генерируем ответ
                return QoreFront\ResponseGenerator::get(
                    # - Command: update form
                    Qore::service(\Qore\Form\Decorator\QoreFront::class)->decorate($fm)
                );
            }

        } else {

            return QoreFront\ResponseGenerator::get(
                $this->getFrontProtocol()
                    ->component(
                        $modal->setTitle('Создание нового атрибута')
                            ->component(Qore::service(\Qore\Form\Decorator\QoreFront::class)->decorate($fm))
                            ->run('open')
                    )
            );
        }
    }

    /**
     * runAttributesUpdate
     *
     */
    private function runAttributesUpdate()
    {
        $routeResult = $this->request->getAttribute(RouteResult::class, null);
        $routeParams = $routeResult->getMatchedParams();

        $mm = Qore::service(\Qore\ORM\ModelManager::class);
        $result = $mm('QSynapse:SynapseAttributes')
            ->where(function($_where) use ($routeParams) {
                $_where(['@this.id' => $routeParams['id']]);
            })
            ->one();

        $fm = $this->getAttributesForm();
        $ig = Qore::service(InterfaceGateway::class);
        $modal = $ig(Modal::class, 'update-synpase-attribute');

        if ($this->request->getMethod() === 'POST') {

            $fm->setData($this->request->getParsedBody());

            if ($fm->isValid()) {

                $data = $fm->getData();

                # - Save form data through Qore\ORM
                $mm($result->combine($data))->save();

                return QoreFront\ResponseGenerator::get(
                    # - Command: modal close
                    $modal->run('close'),
                    # - Command: reload table
                    $ig('synapse-attributes')->run('reload')
                );

            } else {
                # - Generate response for form
                return QoreFront\ResponseGenerator::get(
                    # - Command: update form
                    Qore::service(\Qore\Form\Decorator\QoreFront::class)->decorate($fm)
                );
            }

        } else {

            $fm->setData($result);
            return QoreFront\ResponseGenerator::get(
                $this->getFrontProtocol()
                    ->component(
                        $modal->setTitle('Редактирование синапса')
                            ->component(Qore::service(\Qore\Form\Decorator\QoreFront::class)->decorate($fm))
                            ->run('open')
                    )
            );
        }
    }

    /**
     * runAttributesDelete
     *
     */
    private function runAttributesDelete()
    {
        $routeResult = $this->request->getAttribute(RouteResult::class, null);
        $routeParams = $routeResult->getMatchedParams();

        ($mm = Qore::service(\Qore\ORM\ModelManager::class))(
            $mm('QSynapse:SynapseAttributes', ['id' => $routeParams['id']])
        )->delete();

        $ig = Qore::service(InterfaceGateway::class);
        return QoreFront\ResponseGenerator::get(
            $ig('synapse-attributes')->run('reload')
        );
    }

    /**
     * getAttributesList
     *
     */
    private function getAttributesComponent($_synapseId)
    {
        $routeResult = $this->request->getAttribute(RouteResult::class, null);
        $routeParams = $routeResult->getMatchedParams();
        $ig = Qore::service(InterfaceGateway::class);

        return $ig(Table::class, 'synapse-attributes')
            ->setActions([
                'create' => [
                    'icon' => 'fa fa-plus',
                    'actionUri' => Qore::service(UrlHelper::class)->generate($this->routeName('attributes.create'), $routeParams)
                ],
            ])->setTableOptions([
                'url' => Qore::service(UrlHelper::class)->generate($this->routeName('attributes.reload'), $routeParams)
            ])
            ->setTableData($this->getAttributesColumns(), $this->getAttributes($_synapseId));
    }

    /**
     * getAttributes
     *
     * @param mixed $_iSynapse
     */
    private function getAttributes($_iSynapse)
    {
        $mm = Qore::service(\Qore\ORM\ModelManager::class);
        return $mm('QSynapse:SynapseAttributes')
            ->where(function($_where) use ($_iSynapse){
                $_where(['@this.iSynapse' => $_iSynapse]);
            })
            ->all();
    }

    /**
     * getAttributesColumns
     *
     */
    private function getAttributesColumns()
    {
        return [
            'id' => [
                'label' => '#',
                'model-path' => 'id',
                'class-header' => 'col-sm-1 text-center',
                'class-column' => 'col-sm-1 text-center',
            ],
            'label' => [
                'label' => 'Ярлык',
                'class-header' => 'col-sm-2',
            ],
            'name' => [
                'label' => 'Атрибут',
                'class-header' => 'col-sm-2',
            ],
            'type' => [
                'label' => 'Тип',
                'class-header' => 'col-sm-2',
                'transform' => function($_item) {
                    $types = Entity\SynapseAttribute::getTypes();
                    return $types[$_item['type']]['label'] ?? 'Не задан';
                },
            ],
            'description' => [
                'label' => 'Описание',
                'class-header' => 'col-sm-3',
            ],
            'table-actions' => [
                'label' => 'Действия',
                'class-header' => 'col-sm-2 text-center',
                'class-column' => 'col-sm-2 text-center',
                'actions' => [
                    'update' => [
                        'label' => 'Редактировать',
                        'icon' => 'fas fa-pen',
                        'actionUri' => function($_data) {
                            return Qore::service(UrlHelper::class)->generate($this->routeName('attributes.update'), ['id' => $_data->id]);
                        },
                    ],
                    'delete' => [
                        'label' => 'Удалить',
                        'icon' => 'fa fa-trash',
                        'actionUri' => function($_data) {
                            return Qore::service(UrlHelper::class)->generate($this->routeName('attributes.delete'), ['id' => $_data->id]);
                        },
                        'confirm' => function($_data) {
                            return [
                                'title' => 'Удаление атрибута синапса',
                                'message' => sprintf('Вы действительно хотите удалить атрибут "%s"?', $_data->name)
                            ];
                        },
                    ],
                ],
            ]
        ];
    }

    /**
     * getAttributesForm
     *
     */
    private function getAttributesForm()
    {
        $routeResult = $this->request->getAttribute(RouteResult::class);
        $routeParams = $routeResult->getMatchedParams();
        $isNew = $routeResult->getMatchedRouteName() === $this->routeName('attributes.create');

        $types = (new \Qore\Collection\Collection(Entity\SynapseAttribute::getTypes()))
            ->map(function($v, $k){
                return [
                    'id' => $k,
                    'label' => $v['label'],
                ];
            })
            ->toArray();

        $formFields = [
            'name' => [
                'type' => \Qore\Form\Field\Text::class,
                'label'=> 'Название*',
                'placeholder' => 'Введите название синапса',
                'info' => 'название должно быть уникальным и состоять только из латинских символов нижнего/верхнего регистра, а также цифр',
                'validators' => [
                    [
                        'type' => \Qore\Form\Validator\Required::class,
                        'message' => 'Поле название обязательно.',
                        'break' => true,
                    ],
                    [
                        'type' => \Qore\Form\Validator\Callback::class,
                        'message' => 'Такой атрибут уже существует',
                        'options' => [
                            'callback' => function($_value, $_isNew, $_id) {
                                $mm = Qore::service(\Qore\ORM\ModelManager::class);

                                if ($_isNew) {
                                    $iSynapse = $_id;
                                } else {
                                    $attribute = $mm('QSynapse:SynapseAttributes')
                                        ->where(function($_where) use ($_value, $_id){
                                            $_where(['@this.id' => $_id]);
                                        })->one();
                                    $iSynapse = $attribute['iSynapse'];
                                }

                                $attribute = $mm('QSynapse:SynapseAttributes')
                                    ->where(function($_where) use ($_value, $iSynapse){
                                        $_where([
                                            '@this.name' => $_value,
                                            '@this.iSynapse' => $iSynapse
                                        ]);
                                    })->one();

                                if (is_null($_id)) {
                                    return $attribute ? false : true;
                                } else {
                                    return is_null($attribute) || (int)$attribute->id == (int)$_id;
                                }
                            },
                            'callbackOptions' => [
                                '_isNew' => $isNew,
                                '_id' => $isNew ? $routeParams['synapse-id'] : $routeParams['id']
                            ],
                        ],
                        'break' => true,
                    ],
                    [
                        'type' => \Qore\Form\Validator\Regex::class,
                        'options' => [
                            'pattern' => '/^[A-Za-z0-9]+$/'
                        ],
                        'message' => 'Неправильно заполнено поле',
                        'break' => true,
                    ],
                ]
            ],
            'label' => [
                'type' => \Qore\Form\Field\Text::class,
                'label'=> 'Ярлык*',
                'placeholder' => 'Введите название ярлыка',
                'info' => 'ярлык - служит для более понятной идентификации атрибута',
                'validators' => [
                    [
                        'type' => \Qore\Form\Validator\Required::class,
                        'message' => 'Поле название обязательно.',
                        'break' => true,
                    ],
                    [
                        'type' => \Qore\Form\Validator\Regex::class,
                        'options' => [
                            'pattern' => '/^[A-zА-я0-9 \-\/]+$/u'
                        ],
                        'message' => 'Неправильно заполнено поле ([A-zА-я0-9 ])',
                        'break' => true,
                    ],
                ]
            ],
            'type' => [
                'type' => \Qore\Form\Field\Select::class,
                'label'=> 'Тип атрибута',
                'placeholder' => 'Выберите тип атрибута',
                'info' => 'тип атрибута соответсвует типу столбца в БД',
                'options' => $types,
                'validators' => [
                    [
                        'type' => \Qore\Form\Validator\InArray::class,
                        'message' => 'Выбран неверный вариант типа',
                        'break' => false,
                        'options' => [
                            'haystack' => array_keys(Entity\SynapseAttribute::getTypes()),
                        ]
                    ],
                ]
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
     * runRelationsReload
     *
     */
    private function runRelationsReload()
    {
        $routeResult = $this->request->getAttribute(RouteResult::class, null);
        $routeParams = $routeResult->getMatchedParams();
        return QoreFront\ResponseGenerator::get($this->getRelationsComponent($routeParams['synapse-id']));
    }

    /**
     * runRelationsCreate
     *
     */
    private function runRelationsCreate()
    {
        $routeResult = $this->request->getAttribute(RouteResult::class, null);
        $routeParams = $routeResult->getMatchedParams();

        if (! isset($routeParams['synapse-id'])) {
            return JsonResponse([]);
        }

        $fm = $this->getRelationsForm();
        $ig = Qore::service(InterfaceGateway::class);
        $modal = $ig(Modal::class, 'create-synapse-relation');

        if ($this->request->getMethod() === 'POST') {

            $fm->setData($this->request->getParsedBody());

            if ($fm->isValid()) {
                # - Save form data through Qore\ORM
                ($mm = Qore::service(\Qore\ORM\ModelManager::class))(
                    $mm('QSynapse:SynapseRelations', $fm->getData())
                )->save();

                return QoreFront\ResponseGenerator::get(
                    # - Command: modal close
                    $modal->run('close'),
                    # - Command: reload table
                    $ig('synapse-relations')->run('reload')
                );

            } else {
                # - Генерируем ответ
                return QoreFront\ResponseGenerator::get(
                    # - Command: update form
                    Qore::service(\Qore\Form\Decorator\QoreFront::class)->decorate($fm)
                );
            }

        } else {

            $fm->setData(['iSynapseFrom' => $routeParams['synapse-id']]);

            return QoreFront\ResponseGenerator::get(
                $this->getFrontProtocol()
                    ->component(
                        $modal->setTitle('Создание новой связи')
                            ->component(Qore::service(\Qore\Form\Decorator\QoreFront::class)->decorate($fm))
                            ->run('open')
                    )
            );
        }
    }

    /**
     * runRelationsUpdate
     *
     */
    private function runRelationsUpdate()
    {
        $routeResult = $this->request->getAttribute(RouteResult::class, null);
        $routeParams = $routeResult->getMatchedParams();

        $mm = Qore::service(\Qore\ORM\ModelManager::class);
        $result = $mm('QSynapse:SynapseRelations')
            ->where(function($_where) use ($routeParams) {
                $_where(['@this.id' => $routeParams['id']]);
            })
            ->one();

        $fm = $this->getRelationsForm();
        $ig = Qore::service(InterfaceGateway::class);
        $modal = $ig(Modal::class, 'update-synapse-relation');

        if ($this->request->getMethod() === 'POST') {

            $fm->setData($this->request->getParsedBody());

            if ($fm->isValid()) {

                $data = $fm->getData();

                # - Save form data through Qore\ORM
                $mm($result->combine($data))->save();

                return QoreFront\ResponseGenerator::get(
                    # - Command: modal close
                    $modal->run('close'),
                    # - Command: reload table
                    $ig('synapse-relations')->run('reload')
                );

            } else {
                # - Generate response for form
                return QoreFront\ResponseGenerator::get(
                    # - Command: update form
                    Qore::service(\Qore\Form\Decorator\QoreFront::class)->decorate($fm)
                );
            }

        } else {

            $fm->setData([
                'iParent' => $routeParams['id'] ?? 0,
            ]);

            $fm->setData($result);

            return QoreFront\ResponseGenerator::get(
                $this->getFrontProtocol()
                    ->component(
                        $modal->setTitle('Редактирование синапса')
                            ->component(Qore::service(\Qore\Form\Decorator\QoreFront::class)->decorate($fm))
                            ->run('open')
                    )
                );
        }
    }

    /**
     * runRelationsDelete
     *
     */
    private function runRelationsDelete()
    {
        $routeResult = $this->request->getAttribute(RouteResult::class, null);
        $routeParams = $routeResult->getMatchedParams();

        ($mm = Qore::service(\Qore\ORM\ModelManager::class))(
            $mm('QSynapse:SynapseRelations', ['id' => $routeParams['id']])
        )->delete();
        $ig = Qore::service(InterfaceGateway::class);

        return QoreFront\ResponseGenerator::get(
            $ig('synapse-relations')->run('reload')
        );
    }

    /**
     * getRelationsList
     *
     */
    private function getRelationsComponent($_synapseId)
    {
        $routeResult = $this->request->getAttribute(RouteResult::class, null);
        $routeParams = $routeResult->getMatchedParams();

        $ig = Qore::service(InterfaceGateway::class);

        return $ig(Table::class, 'synapse-relations')
            ->setActions([
                'create' => [
                    'icon' => 'fa fa-plus',
                    'actionUri' => Qore::service(UrlHelper::class)->generate($this->routeName('relations.create'), $routeParams)
                ]
            ])->setTableOptions([
                'url' => Qore::service(UrlHelper::class)->generate($this->routeName('relations.reload'), $routeParams)
            ])
            ->setTableData($this->getRelationsColumns(), $this->getRelations($_synapseId));
    }

    /**
     * getRelations
     *
     * @param mixed $_iSynapse
     */
    private function getRelations($_iSynapse)
    {
        $mm = Qore::service(\Qore\ORM\ModelManager::class);
        return $mm('QSynapse:SynapseRelations')
            ->with('synapseTo')
            ->with('synapseFrom')
            ->where(function($_where) use ($_iSynapse){
                $_where(['@this.iSynapseFrom' => $_iSynapse])
                    ->OR(['@this.iSynapseTo' => $_iSynapse]);
            })
            ->all();
    }

    /**
     * getRelationsColumns
     *
     */
    private function getRelationsColumns()
    {
        return [
            'id' => [
                'label' => '#',
                'class-header' => 'col-sm-1 text-center',
                'class-column' => 'col-sm-1 text-center',
            ],
            'synapseFrom.name' => [
                'label' => 'Связь от',
                'class-header' => 'col-sm-2 text-center',
                'class-column' => 'col-sm-2 text-center',
                'transform' => function($_item) {
                    return $_item->synapseFrom->name . '@' . $_item->synapseAliasFrom;
                },
            ],
            'synapseTo.name' => [
                'label' => 'Связь к',
                'class-header' => 'col-sm-2 text-center',
                'class-column' => 'col-sm-2 text-center',
                'transform' => function($_item) {
                    return $_item->synapseTo->name . '@' . $_item->synapseAliasTo;
                },
            ],
            'type' => [
                'label' => 'Тип',
                'class-header' => 'col-sm-2 text-center',
                'class-column' => 'col-sm-2 text-center',
                'transform' => function($_item) {
                    $types = Entity\SynapseRelation::getTypes();
                    return $types[$_item['type']]['label'] ?? 'Не задан';
                },
            ],
            'description' => [
                'label' => 'Комментарий',
                'class-header' => 'col-sm-4',
            ],
            'table-actions' => [
                'label' => 'Действия',
                'class-header' => 'col-sm-1 text-center',
                'class-column' => 'col-sm-1 text-center',
                'actions' => [
                    'update' => [
                        'label' => 'Редактировать',
                        'icon' => 'fas fa-pen',
                        'actionUri' => function($_data) {
                            return Qore::service(UrlHelper::class)->generate($this->routeName('relations.update'), ['id' => $_data->id]);
                        },
                    ],
                    'delete' => [
                        'label' => 'Удалить',
                        'icon' => 'fa fa-trash',
                        'actionUri' => function($_data) {
                            return Qore::service(UrlHelper::class)->generate($this->routeName('relations.delete'), ['id' => $_data->id]);
                        },
                        'confirm' => function($_data) {
                            return [
                                'title' => 'Удаление связи синапса',
                                'message' => sprintf('Вы действительно хотите удалить связь "%s"?', $_data->id)
                            ];
                        },
                    ],
                ],
            ]
        ];
    }

    /**
     * getRelationsForm
     *
     */
    private function getRelationsForm()
    {
        $routeResult = $this->request->getAttribute(RouteResult::class);
        $routeParams = $routeResult->getMatchedParams();
        $isNew = $routeResult->getMatchedRouteName() === $this->routeName('relations.create');

        $types = (new \Qore\Collection\Collection(Entity\SynapseRelation::getTypes()))
            ->map(function($v, $k){
                return [
                    'id' => $k,
                    'label' => $v['label'],
                ];
            })
            ->toArray();

        $mm = Qore::service(\Qore\ORM\ModelManager::class);
        $synapses = $mm('QSynapse:Synapses')->all();

        $formFields = [
            'iSynapseFrom' => [
                'type' => \Qore\Form\Field\TreeSelect::class,
                'label'=> 'Источник связи',
                'placeholder' => 'Выбрети синапс для связи',
                'options' => $synapses->map(function($v, $k){
                    return [
                        'id' => (int)$v['id'],
                        'id_parent' => (int)$v['iParent'],
                        'label' => $v['name'],
                    ];
                })->nest('id', 'id_parent'),
                'validators' => [
                    [
                        'type' => \Qore\Form\Validator\InArray::class,
                        'message' => 'Выбран неверный вариант типа',
                        'break' => false,
                        'options' => [
                            'haystack' => $synapses->extract('id')->toArray(),
                        ]
                    ],
                ]
            ],
            'synapseAliasFrom' => [
                'type' => \Qore\Form\Field\Text::class,
                'label'=> 'Псевдоним связи источника*',
                'placeholder' => 'Введите псевдоним связи',
                'info' => 'псевдоним должен быть уникальным и состоять только из латинских символов нижнего/верхнего регистра, а также цифр',
                'validators' => [
                    [
                        'type' => \Qore\Form\Validator\Required::class,
                        'message' => 'Поле название обязательно.',
                        'break' => true,
                    ],
                    [
                        'type' => \Qore\Form\Validator\Regex::class,
                        'options' => [
                            'pattern' => '/^[A-Za-z0-9]+$/'
                        ],
                        'message' => 'Неправильно заполнено поле',
                        'break' => true,
                    ],
                ]
            ],
            'iSynapseTo' => [
                'type' => \Qore\Form\Field\TreeSelect::class,
                'label'=> 'Связать с',
                'placeholder' => 'Выбрети синапс для связи',
                'options' => $synapses->map(function($v, $k){
                    return [
                        'id' => (int)$v['id'],
                        'id_parent' => (int)$v['iParent'],
                        'label' => $v['name'],
                    ];
                })->nest('id', 'id_parent'),
                'validators' => [
                    [
                        'type' => \Qore\Form\Validator\InArray::class,
                        'message' => 'Выбран неверный вариант типа',
                        'break' => false,
                        'options' => [
                            'haystack' => $synapses->extract('id')->toArray(),
                        ]
                    ],
                ]
            ],
            'synapseAliasTo' => [
                'type' => \Qore\Form\Field\Text::class,
                'label'=> 'Псевдоним связи приемника*',
                'placeholder' => 'Введите псевдоним связи',
                'info' => 'псевдоним должен быть уникальным и состоять только из латинских символов нижнего/верхнего регистра, а также цифр',
                'validators' => [
                    [
                        'type' => \Qore\Form\Validator\Required::class,
                        'message' => 'Поле название обязательно.',
                        'break' => true,
                    ],
                    [
                        'type' => \Qore\Form\Validator\Regex::class,
                        'options' => [
                            'pattern' => '/^[A-Za-z0-9]+$/'
                        ],
                        'message' => 'Неправильно заполнено поле',
                        'break' => true,
                    ],
                ]
            ],
            'type' => [
                'type' => \Qore\Form\Field\Select::class,
                'label'=> 'Тип связи',
                'placeholder' => 'Выберите тип связи',
                'options' => $types,
                'validators' => [
                    [
                        'type' => \Qore\Form\Validator\InArray::class,
                        'message' => 'Выбран неверный вариант типа',
                        'break' => false,
                        'options' => [
                            'haystack' => array_keys(Entity\SynapseRelation::getTypes()),
                        ]
                    ],
                ]
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
                'label' => $isNew ? 'Создать' : 'Сохранить',
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
     * runServicesReload
     *
     */
    private function runServicesReload()
    {
        $routeResult = $this->request->getAttribute(RouteResult::class, null);
        $routeParams = $routeResult->getMatchedParams();
        return QoreFront\ResponseGenerator::get($this->getServicesComponent($routeParams['synapse-id']));
    }

    /**
     * runServicesCreate
     *
     */
    private function runServicesCreate()
    {
        $routeResult = $this->request->getAttribute(RouteResult::class, null);
        $routeParams = $routeResult->getMatchedParams();

        if (! isset($routeParams['synapse-id'])) {
            return JsonResponse([]);
        }

        $fm = $this->getServicesForm();
        $ig = Qore::service(InterfaceGateway::class);
        $modal = $ig(Modal::class, 'create-synapse-service');

        if ($this->request->getMethod() === 'POST') {

            $fm->setData($this->request->getParsedBody());

            if ($fm->isValid()) {
                # - Save form data through Qore\ORM
                ($mm = Qore::service(\Qore\ORM\ModelManager::class))(
                    $mm('QSynapse:SynapseServices', array_merge($fm->getData(), ['iSynapse' => $routeParams['synapse-id']]))
                )->save();

                return QoreFront\ResponseGenerator::get(
                    # - Command: modal close
                    $modal->run('close'),
                    # - Command: reload table
                    $ig(Table::class, 'synapse-services')->run('reload')
                );

            } else {
                # - Генерируем ответ
                return QoreFront\ResponseGenerator::get(
                    # - Command: update form
                    Qore::service(\Qore\Form\Decorator\QoreFront::class)->decorate($fm)
                );
            }

        } else {

            return QoreFront\ResponseGenerator::get(
                $this->getFrontProtocol()
                    ->component(
                        $modal->setTitle('Создание нового сервиса')
                            ->component(Qore::service(\Qore\Form\Decorator\QoreFront::class)->decorate($fm))->run('open')
                    )
            );
        }
    }

    /**
     * runServicesUpdate
     *
     */
    private function runServicesUpdate()
    {
        $routeResult = $this->request->getAttribute(RouteResult::class, null);
        $routeParams = $routeResult->getMatchedParams();

        $mm = Qore::service(\Qore\ORM\ModelManager::class);
        $result = $mm('QSynapse:SynapseServices')
            ->where(function($_where) use ($routeParams) {
                $_where(['@this.id' => $routeParams['id']]);
            })
            ->one();

        $fm = $this->getServicesForm();
        $ig = Qore::service(InterfaceGateway::class);
        $modal = $ig(Modal::class, 'update-synapse-service');

        if ($this->request->getMethod() === 'POST') {

            $fm->setData($this->request->getParsedBody());

            if ($fm->isValid()) {

                $data = $fm->getData();

                # - Save form data through Qore\ORM
                $mm($result->combine($data))->save();

                return QoreFront\ResponseGenerator::get(
                    # - Command: modal close
                    $modal->run('close'),
                    # - Command: reload table
                    $ig('synapse-services')->run('reload')
                );

            } else {
                # - Generate response for form
                return QoreFront\ResponseGenerator::get(
                    # - Command: update form
                    Qore::service(\Qore\Form\Decorator\QoreFront::class)->decorate($fm)
                );
            }

        } else {

            $fm->setData($result);

            return QoreFront\ResponseGenerator::get(
                $this->getFrontProtocol()
                    ->component(
                        $modal->setTitle('Редактирование сервиса')
                            ->component(Qore::service(\Qore\Form\Decorator\QoreFront::class)->decorate($fm))
                            ->run('open')
                    ),
            );
        }
    }

    /**
     * runServicesDelete
     *
     */
    private function runServicesDelete()
    {
        $routeResult = $this->request->getAttribute(RouteResult::class, null);
        $routeParams = $routeResult->getMatchedParams();

        ($mm = Qore::service(\Qore\ORM\ModelManager::class))(
            $mm('QSynapse:SynapseServices', ['id' => $routeParams['id']])
        )->delete();

        $ig = Qore::service(InterfaceGateway::class);

        return QoreFront\ResponseGenerator::get(
            $ig('synapse-services')->run('reload')
        );
    }

    /**
     * getServicesList
     *
     */
    private function getServicesComponent($_synapseId)
    {
        $routeResult = $this->request->getAttribute(RouteResult::class, null);
        $routeParams = $routeResult->getMatchedParams();

        $ig = Qore::service(InterfaceGateway::class);

        return $ig(Table::class, 'synapse-services')
            ->setActions([
                'create' => [
                    'icon' => 'fa fa-plus',
                    'actionUri' => Qore::service(UrlHelper::class)->generate($this->routeName('services.create'), $routeParams)
                ]
            ])->setTableOptions([
                'url' => Qore::service(UrlHelper::class)->generate($this->routeName('services.reload'), $routeParams)
            ])
            ->setTableData($this->getServicesColumns(), $this->getServices($_synapseId));
    }

    /**
     * getServices
     *
     * @param mixed $_iSynapse
     */
    private function getServices($_iSynapse)
    {
        $mm = Qore::service(\Qore\ORM\ModelManager::class);
        return $mm('QSynapse:SynapseServices')
            ->where(function($_where) use ($_iSynapse){
                $_where(['@this.iSynapse' => $_iSynapse]);
            })
            ->all();
    }

    /**
     * getServicesColumns
     *
     */
    private function getServicesColumns()
    {
        return [
            'id' => [
                'label' => '#',
                'class-header' => 'col-sm-1 text-center',
                'class-column' => 'col-sm-1 text-center',
            ],
            'name' => [
                'label' => 'Сервис',
                'class-header' => 'col-sm-2 text-center',
                'class-column' => 'col-sm-2 text-center',
            ],
            'description' => [
                'label' => 'Описание',
                'class-header' => 'col-sm-7',
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
                            return Qore::service(UrlHelper::class)->generate($this->routeName(SynapseServiceStructure::class, 'index'), ['service-id' => $_data->id]);
                        },
                    ],
                    'update' => [
                        'label' => 'Редактировать',
                        'icon' => 'fas fa-pen',
                        'actionUri' => function($_data) {
                            return Qore::service(UrlHelper::class)->generate($this->routeName('services.update'), ['id' => $_data->id]);
                        },
                    ],
                    'delete' => [
                        'label' => 'Удалить',
                        'icon' => 'fa fa-trash',
                        'actionUri' => function($_data) {
                            return Qore::service(UrlHelper::class)->generate($this->routeName('services.delete'), ['id' => $_data->id]);
                        },
                        'confirm' => function($_data) {
                            return [
                                'title' => 'Удаление сервиса синапса',
                                'message' => sprintf('Вы действительно хотите удалить сервис "%s"?', $_data->id)
                            ];
                        },
                    ],
                ],
            ]
        ];
    }

    /**
     * getAttributesForm
     *
     */
    private function getServicesForm()
    {
        $routeResult = $this->request->getAttribute(RouteResult::class);
        $routeParams = $routeResult->getMatchedParams();
        $isNew = $routeResult->getMatchedRouteName() === $this->routeName('services.create');

        $mm = Qore::service(\Qore\ORM\ModelManager::class);

        $formFields = [
            'name' => [
                'type' => \Qore\Form\Field\Text::class,
                'label'=> 'Название*',
                'placeholder' => 'Введите название сервиса',
                'info' => 'название должно быть уникальным в пределах данного синапса и состоять только из латинских символов нижнего/верхнего регистра, а также цифр',
                'validators' => [
                    [
                        'type' => \Qore\Form\Validator\Required::class,
                        'message' => 'Поле название обязательно.',
                        'break' => true,
                    ],
                    [
                        'type' => \Qore\Form\Validator\Callback::class,
                        'message' => 'Такой сервис уже существует',
                        'options' => [
                            'callback' => function($_value, $_isNew, $_id) {
                                $mm = Qore::service(\Qore\ORM\ModelManager::class);

                                if ($_isNew) {
                                    $iSynapse = $_id;
                                } else {
                                    $service = $mm('QSynapse:SynapseServices')
                                        ->where(function($_where) use ($_value, $_id){
                                            $_where(['@this.id' => $_id]);
                                        })->one();
                                    $iSynapse = $service['iSynapse'];
                                }

                                $service = $mm('QSynapse:SynapseServices')
                                    ->where(function($_where) use ($_value, $iSynapse){
                                        $_where([
                                            '@this.name' => $_value,
                                            '@this.iSynapse' => $iSynapse,
                                        ]);
                                    })->one();

                                if ($_isNew) {
                                    return $service ? false : true;
                                } else {
                                    return is_null($service) || (int)$service->id == (int)$_id;
                                }
                            },
                            'callbackOptions' => [
                                '_isNew' => $isNew,
                                '_id' => $isNew ? $routeParams['synapse-id'] : $routeParams['id']
                            ],
                        ],
                        'break' => true,
                    ],
                    [
                        'type' => \Qore\Form\Validator\Regex::class,
                        'options' => [
                            'pattern' => '/^[A-Za-z0-9]+$/'
                        ],
                        'message' => 'Неправильно заполнено поле',
                        'break' => true,
                    ],
                ]
            ],
            'label' => [
                'type' => \Qore\Form\Field\Text::class,
                'label'=> 'Ярлык*',
                'placeholder' => 'Укажите ярлык сервиса',
                'info' => 'для более понятной идентификации сервиса в системе интерфейсов используется ярлык',
                'validators' => [
                    [
                        'type' => \Qore\Form\Validator\Required::class,
                        'message' => 'Поле название обязательно.',
                        'break' => true,
                    ],
                    [
                        'type' => \Qore\Form\Validator\Regex::class,
                        'options' => [
                            'pattern' => '/^[A-zА-я0-9 \-]+$/u'
                        ],
                        'message' => 'Неправильно заполнено поле ([A-zА-я0-9 -])',
                        'break' => true,
                    ],
                ]
            ],
            'index' => [
                'type' => \Qore\Form\Field\Switcher::class,
                'label'=> 'Индексировать данные',
                'placeholder' => '',
                'info' => 'класс данного сервиса должен соответствовать интерфейсу индексации'
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

}
