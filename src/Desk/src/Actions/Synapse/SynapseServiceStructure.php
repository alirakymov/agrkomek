<?php

namespace Qore\Desk\Actions\Synapse;


use Qore\InterfaceGateway\Component\Modal;
use Qore\InterfaceGateway\Component\Table;
use Qore\InterfaceGateway\Component\Tabs\Tabs;
use Qore\InterfaceGateway\InterfaceGateway;
use Qore\Qore;
use Qore\Collection;
use Qore\Front as QoreFront;
use Qore\Router\RouteCollector;
use Qore\SynapseManager\Structure\Entity;
use Qore\Desk\Actions\BaseAction;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Mezzio\Helper\UrlHelper;
use Mezzio\Router\RouteResult;

/**
 * Class: SynapseServiceStructure
 *
 * @see BaseAction
 */
class SynapseServiceStructure extends BaseAction
{
    /**
     * Return routes array
     *
     * @return array
     */
    public function routes(RouteCollector $_router) : void
    {
        $_router->group('/service-service-structure', null, function($_router) {
            $_router->any('/{service-id:[0-9]+}', 'index');
            $_router->group('/subjects', 'subjects', function($_router) {
                $_router->any('/reload/{service-id:[0-9]+}', 'reload');
                $_router->any('/create/{service-id:[0-9]+}', 'create');
                $_router->any('/update/{id:\d+}', 'update');
                $_router->any('/delete/{id:\d+}', 'delete');
            });
            $_router->group('/forms', 'forms', function($_router) {
                $_router->any('/reload/{service-id:[0-9]+}', 'reload');
                $_router->any('/create/{service-id:[0-9]+}', 'create');
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

        if (! $service = $this->getService($routeParams['service-id'])) {
            return new JsonResponse([]);
        }

        $ig = Qore::service(InterfaceGateway::class);

        $tabsComponent = $ig(Tabs::class, 'service-structure')
            ->setTitle(sprintf('Структура сервиса "%s"', $service->name))
            ->tab('subjects', function($_tab) use ($service) {
                $_tab->setLabel('Субъекты')->component($this->getSubjectsComponent($service->id));
            })->tab('forms', function($_tab) use ($service) {
                $_tab->setLabel('Формы')->component($this->getFormsComponent($service->id));
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
     * getService
     *
     * @param mixed $_id
     */
    private function getService($_id)
    {
        $mm = Qore::service(\Qore\ORM\ModelManager::class);
        return $mm('QSynapse:SynapseServices')
            ->where(function($_where) use ($_id){
                $_where(['@this.id' => $_id]);
            })
            ->one();
    }


    /**
     * runSubjectsReload
     *
     */
    private function runSubjectsReload()
    {
        $routeResult = $this->request->getAttribute(RouteResult::class, null);
        $routeParams = $routeResult->getMatchedParams();
        return QoreFront\ResponseGenerator::get($this->getSubjectsComponent($routeParams['service-id']));
    }

    /**
     * runSubjectsCreate
     *
     */
    private function runSubjectsCreate()
    {
        $routeResult = $this->request->getAttribute(RouteResult::class, null);
        $routeParams = $routeResult->getMatchedParams();

        if (! isset($routeParams['service-id'])) {
            return JsonResponse([]);
        }

        $fm = $this->getSubjectsForm();
        $ig = Qore::service(InterfaceGateway::class);
        $modal = $ig(Modal::class, 'create-service-subject');

        if ($this->request->getMethod() === 'POST') {

            $fm->setData($this->request->getParsedBody());

            if ($fm->isValid()) {
                # - Save form data through Qore\ORM
                ($mm = Qore::service(\Qore\ORM\ModelManager::class))(
                    $mm('QSynapse:SynapseServiceSubjects', array_merge($fm->getData(), ['iSynapseServiceFrom' => $routeParams['service-id']]))
                )->save();

                return QoreFront\ResponseGenerator::get(
                    # - Command: modal close
                    $modal->run('close'),
                    # - Command: reload table
                    $ig('service-subjects')->run('reload')
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
                    ),
            );
        }
    }

    /**
     * runSubjectsUpdate
     *
     */
    private function runSubjectsUpdate()
    {
        $routeResult = $this->request->getAttribute(RouteResult::class, null);
        $routeParams = $routeResult->getMatchedParams();

        $mm = Qore::service(\Qore\ORM\ModelManager::class);
        $result = $mm('QSynapse:SynapseServiceSubjects')->with('relation')
            ->with('serviceFrom', function($_gw){
                $_gw->with('synapse');
            })
            ->where(function($_where) use ($routeParams) {
                $_where(['@this.id' => $routeParams['id']]);
            })
            ->one();

        $fm = $this->getSubjectsForm();
        $ig = Qore::service(InterfaceGateway::class);
        $modal = $ig(Modal::class, 'update-service-subject');

        if ($this->request->getMethod() === 'POST') {

            $fm->setData($this->request->getParsedBody());

            if ($fm->isValid()) {
                # - Save form data through Qore\ORM
                $mm($result->combine($fm->getData()))->save();

                return QoreFront\ResponseGenerator::get(
                    # - Command: modal close
                    $modal->run('close'),
                    # - Command: reload table
                    $ig('service-subjects')->run('reload')
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

            $fm->setData($result->prepareDataToForm());

            return QoreFront\ResponseGenerator::get(
                $this->getFrontProtocol()
                    ->component(
                        $modal->setTitle('Редактирование субъетка сервиса')
                            ->component(Qore::service(\Qore\Form\Decorator\QoreFront::class)->decorate($fm))
                            ->run('open')
                    )
            );
        }
    }

    /**
     * runSubjectsDelete
     *
     */
    private function runSubjectsDelete()
    {
        Qore::service('debug')->message($this->routeName('delete'));

        $routeResult = $this->request->getAttribute(RouteResult::class, null);
        $routeParams = $routeResult->getMatchedParams();

        ($mm = Qore::service(\Qore\ORM\ModelManager::class))(
            $mm('QSynapse:SynapseServiceSubjects', ['id' => $routeParams['id']])
        )->delete();

        $ig = Qore::service(InterfaceGateway::class);

        return QoreFront\ResponseGenerator::get(
            $ig('service-subjects')->run('reload')
        );
    }

    /**
     * getSubjectsList
     *
     */
    private function getSubjectsComponent($_serviceId)
    {
        $routeResult = $this->request->getAttribute(RouteResult::class, null);
        $routeParams = $routeResult->getMatchedParams();

        $ig = Qore::service(InterfaceGateway::class);

        return $ig(Table::class, 'service-subjects')
            ->setActions([
                'create' => [
                    'icon' => 'fa fa-plus',
                    'actionUri' => Qore::service(UrlHelper::class)->generate($this->routeName('subjects.create'), $routeParams)
                ]
            ])->setTableOptions([
                'url' => Qore::service(UrlHelper::class)->generate($this->routeName('subjects.reload'), $routeParams)
            ])
            ->setTableData($this->getSubjectsColumns(), $this->getSubjects($_serviceId));
    }

    /**
     * getSubjects
     *
     * @param mixed $_iSynapse
     */
    private function getSubjects($_iSynapse)
    {
        $mm = Qore::service(\Qore\ORM\ModelManager::class);
        return $mm('QSynapse:SynapseServiceSubjects')
            ->with('relation')
            ->with('serviceFrom', function($_gw) {
                $_gw->with('synapse');
            })
            ->with('serviceTo', function($_gw) {
                $_gw->with('synapse');
            })
            ->where(function($_where) use ($_iSynapse){
                $_where(['@this.iSynapseServiceFrom' => $_iSynapse]);
            })
            ->all();
    }

    /**
     * getSubjectsColumns
     *
     */
    private function getSubjectsColumns()
    {
        return [
            'id' => [
                'label' => '#',
                'model-path' => 'id',
                'class-header' => 'col-sm-1 text-center',
                'class-column' => 'col-sm-1 text-center',
            ],
            'serviceTo.synapse.name' => [
                'label' => 'Синапс',
                'class-header' => 'col-sm-2',
                'transform' => function($_data) {
                    return $_data->getLabel();
                },
            ],
            'serviceTo.label' => [
                'label' => 'Сервис',
                'class-header' => 'col-sm-2',
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
                    'update' => [
                        'label' => 'Редактировать',
                        'icon' => 'fas fa-pen',
                        'actionUri' => function($_data) {
                            return Qore::service(UrlHelper::class)->generate($this->routeName('subjects.update'), ['id' => $_data->id]);
                        },
                    ],
                    'delete' => [
                        'label' => 'Удалить',
                        'icon' => 'fa fa-trash',
                        'actionUri' => function($_data) {
                            return Qore::service(UrlHelper::class)->generate($this->routeName('subjects.delete'), ['id' => $_data->id]);
                        },
                        'confirm' => function($_data) {
                            return [
                                'title' => 'Удаление субъекта сервиса',
                                'message' => sprintf('Вы действительно хотите удалить субъект "%s:%s"?', $_data->serviceTo->synapse->name, $_data->serviceTo->name)
                                // 'message' => sprintf('Вы действительно хотите удалить субъект "%s:%s"?', 'test', 'test')
                            ];
                        },
                    ],
                ],
            ]
        ];
    }

    /**
     * getSubjectsForm
     *
     */
    private function getSubjectsForm()
    {
        $routeResult = $this->request->getAttribute(RouteResult::class);
        $routeParams = $routeResult->getMatchedParams();
        $isNew = $routeResult->getMatchedRouteName() === $this->routeName('subjects.create');

        $mm = Qore::service(\Qore\ORM\ModelManager::class);

        if ($isNew) {
            $service = $mm('QSynapse:SynapseServices')
                ->with('synapse', function($_gw) {
                    $_gw->with('relationsTo', function($_gw) {
                        $_gw->with('synapseFrom', function($_gw) {
                            $_gw->with('services');
                        });
                    })->with('relationsFrom', function($_gw) {
                        $_gw->with('synapseTo', function($_gw) {
                            $_gw->with('services');
                        });
                    });
                })
                ->where(function($_where) use ($routeParams) {
                    $_where(['@this.id' => $routeParams['service-id']]);
                })
                ->one();
        } else {
            $service = $mm('QSynapse:SynapseServices')
                ->with('synapse', function($_gw) {
                    $_gw->with('relationsTo', function($_gw) {
                        $_gw->with('synapseFrom', function($_gw) {
                            $_gw->with('services');
                        });
                    })->with('relationsFrom', function($_gw) {
                        $_gw->with('synapseTo', function($_gw) {
                            $_gw->with('services');
                        });
                    });
                })
                ->with('subjectsFrom', function($_gw) use ($routeParams) {
                    $_gw->where(function($_where) use ($routeParams) {
                        $_where(['@this.id' => $routeParams['id']]);
                    });
                })
                ->one();
        }

        $serviceSubjects = $mm('QSynapse:SynapseServiceSubjects')
            ->where(function($_where) use ($service) {
                $_where(['@this.iSynapseServiceFrom' => $service->id]);
            })
            ->all();

        $usedServicesIds = $serviceSubjects
            ? $serviceSubjects
                ->filter(function($subject) use ($isNew, $routeParams) {
                    // - Если редактируем субъект, то собираем всех кроме текущего
                    return $isNew || (int)$subject->id != (int)$routeParams['id'];
                })
                ->map(function($subject, $key) {
                    return $subject->iSynapseServiceTo;
                })->toArray(false)
            : [];

        $servicesOptions = (new \Qore\Collection\Collection([]))
            ->append($service->synapse->relationsTo
                ->filter(function ($relation) {
                    return $relation->synapseFrom ? true : false;
                })
                ->map(function ($relation) use ($usedServicesIds, $service) {
                    return [
                        'id' => 'r' . $relation->id,
                        'label' => sprintf('%s(%s)', $relation->synapseFrom->name, $relation->synapseAliasTo),
                        'children' => $relation->synapseFrom->services
                            ->filter(function ($service) use ($usedServicesIds) {
                                return ! in_array($service->id, $usedServicesIds);
                            })
                            ->map(function ($service) use ($relation) {
                                return [
                                    'id' => implode(Entity\SynapseServiceSubject::RELATION_DELIMETER, [$relation->id, $service->id, Entity\SynapseServiceSubject::RELATION_TYPE_TO]),
                                    'label' => sprintf('%s(%s)::%s - %s', $relation->synapseFrom->name, $relation->synapseAliasTo, $service->name, $service->label),
                                ];
                            })->toArray(false),
                    ];
                })
            )
            ->append($service->synapse->relationsFrom
                ->filter(function ($relation) {
                    return $relation->synapseTo ? true : false;
                })
                ->map(function ($relation) use ($usedServicesIds)  {
                    return [
                        'id' => 'r' . $relation->id,
                        'label' => sprintf('%s(%s)', $relation->synapseTo->name, $relation->synapseAliasFrom),
                        'children' => $relation->synapseTo->services
                            ->filter(function ($service) use ($usedServicesIds) {
                                return ! in_array($service->id, $usedServicesIds);
                            })
                            ->map(function ($service) use ($relation) {
                                return [
                                    'id' => implode(Entity\SynapseServiceSubject::RELATION_DELIMETER, [$relation->id, $service->id, Entity\SynapseServiceSubject::RELATION_TYPE_FROM]),
                                    'label' => sprintf('%s(%s)::%s - %s', $relation->synapseTo->name, $relation->synapseAliasFrom, $service->name, $service->label),
                                ];
                            })->toArray(false),
                    ];
                })
            );

        $formFields = [
            'relatedSynapseService' => [
                'type' => \Qore\Form\Field\TreeSelect::class,
                'label'=> 'Целевой синапс',
                'validators' => [
                    [
                        'type' => \Qore\Form\Validator\Required::class,
                        'message' => 'Выберите целевой синапс',
                        'break' => false,
                    ],
                    [
                        'type' => \Qore\Form\Validator\Regex::class,
                        'message' => 'Неверный формат целевого синапса',
                        'options' => [
                            'pattern' => '/\d+:\d+/'
                        ],
                        'break' => false,
                    ],
                ],
                'placeholder' => 'Выберите целевой синапс',
                'options' => $servicesOptions->toArray(false),
                'additional' => [
                    'multi' => false,
                    'disable-branch-nodes' => true,
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
     * runFormsReload
     *
     */
    private function runFormsReload()
    {
        $routeResult = $this->request->getAttribute(RouteResult::class, null);
        $routeParams = $routeResult->getMatchedParams();
        return QoreFront\ResponseGenerator::get($this->getFormsComponent($routeParams['service-id']));
    }

    /**
     * runFormsCreate
     *
     */
    private function runFormsCreate()
    {
        $routeResult = $this->request->getAttribute(RouteResult::class, null);
        $routeParams = $routeResult->getMatchedParams();

        if (! isset($routeParams['service-id'])) {
            return JsonResponse([]);
        }

        $fm = $this->getFormsForm();
        $ig = Qore::service(InterfaceGateway::class);
        $modal = $ig(Modal::class, 'create-service-form');

        if ($this->request->getMethod() === 'POST') {

            $fm->setData($this->request->getParsedBody());

            if ($fm->isValid()) {
                # - Save form data through Qore\ORM
                ($mm = Qore::service(\Qore\ORM\ModelManager::class))(
                    $mm('QSynapse:SynapseServiceForms', array_merge($fm->getData(), ['iSynapseService' => $routeParams['service-id']]))
                )->save();

                return QoreFront\ResponseGenerator::get(
                    # - Command: modal close
                    $modal->run('close'),
                    # - Command: reload table
                    $ig('service-forms')->run('reload')
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
                        $modal->setTitle('Создание формы сервиса')
                            ->component(Qore::service(\Qore\Form\Decorator\QoreFront::class)->decorate($fm))
                            ->run('open')
                    )
            );
        }
    }

    /**
     * runFormsUpdate
     *
     */
    private function runFormsUpdate()
    {
        $routeResult = $this->request->getAttribute(RouteResult::class, null);
        $routeParams = $routeResult->getMatchedParams();

        $mm = Qore::service(\Qore\ORM\ModelManager::class);
        $result = $mm('QSynapse:SynapseServiceForms')
            ->where(function($_where) use ($routeParams) {
                $_where(['@this.id' => $routeParams['id']]);
            })
            ->one();

        $fm = $this->getFormsForm();
        $ig = Qore::service(InterfaceGateway::class);
        $modal = $ig(Modal::class, 'update-service-form');

        if ($this->request->getMethod() === 'POST') {

            $fm->setData($this->request->getParsedBody());

            if ($fm->isValid()) {

                $data = $fm->getData();

                Qore::debug($data);
                # - Save form data through Qore\ORM
                $mm($result->combine($fm->getData()))->save();

                return QoreFront\ResponseGenerator::get(
                    # - Command: modal close
                    $modal->run('close'),
                    # - Command: reload table
                    $ig('service-forms')->run('reload')
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
                        $modal->setTitle('Редактирование формы сервиса')
                            ->component(Qore::service(\Qore\Form\Decorator\QoreFront::class)->decorate($fm))
                            ->run('open')
                    )
            );
        }
    }

    /**
     * runFormsDelete
     *
     */
    private function runFormsDelete()
    {
        Qore::service('debug')->message($this->routeName('delete'));

        $routeResult = $this->request->getAttribute(RouteResult::class, null);
        $routeParams = $routeResult->getMatchedParams();

        $ig = Qore::service(InterfaceGateway::class);

        ($mm = Qore::service(\Qore\ORM\ModelManager::class))(
            $mm('QSynapse:SynapseServiceForms', ['id' => $routeParams['id']])
        )->delete();

        return QoreFront\ResponseGenerator::get(
            $ig('service-forms')->run('reload')
        );
    }

    /**
     * getFormsList
     *
     */
    private function getFormsComponent($_serviceId)
    {
        $routeResult = $this->request->getAttribute(RouteResult::class, null);
        $routeParams = $routeResult->getMatchedParams();

        $ig = Qore::service(InterfaceGateway::class);
        return $ig(Table::class, 'service-forms')
            ->setActions([
                'create' => [
                    'icon' => 'fa fa-plus',
                    'actionUri' => Qore::service(UrlHelper::class)->generate($this->routeName('forms.create'), $routeParams)
                ]
            ])->setTableOptions([
                'url' => Qore::service(UrlHelper::class)->generate($this->routeName('forms.reload'), $routeParams)
            ])->setTableData($this->getFormsColumns(), $this->getForms($_serviceId));
    }

    /**
     * getForms
     *
     * @param mixed $_iService
     */
    private function getForms($_iService)
    {
        $mm = Qore::service(\Qore\ORM\ModelManager::class);
        return $mm('QSynapse:SynapseServiceForms')
            ->where(function($_where) use ($_iService){
                $_where(['@this.iSynapseService' => $_iService]);
            })
            ->all();
    }

    /**
     * getFormsColumns
     *
     */
    private function getFormsColumns()
    {
        return [
            'id' => [
                'label' => '#',
                'class-header' => 'col-sm-1 text-center',
                'class-column' => 'col-sm-1 text-center',
            ],
            'name' => [
                'label' => 'Форма',
                'class-header' => 'col-sm-2 text-center',
                'class-column' => 'col-sm-2 text-center',
            ],
            'label' => [
                'label' => 'Ярлык',
                'class-header' => 'col-sm-2 text-center',
                'class-column' => 'col-sm-2 text-center',
            ],
            'description' => [
                'label' => 'Комментарий',
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
                            return Qore::service(UrlHelper::class)->generate($this->routeName(SynapseServiceFormStructure::class, 'index'), ['form-id' => $_data->id]);
                        },
                    ],
                    'update' => [
                        'label' => 'Редактировать',
                        'icon' => 'fas fa-pen',
                        'actionUri' => function($_data) {
                            return Qore::service(UrlHelper::class)->generate($this->routeName('forms.update'), ['id' => $_data->id]);
                        },
                    ],
                    'delete' => [
                        'label' => 'Удалить',
                        'icon' => 'fa fa-trash',
                        'actionUri' => function($_data) {
                            return Qore::service(UrlHelper::class)->generate($this->routeName('forms.delete'), ['id' => $_data->id]);
                        },
                        'confirm' => function($_data) {
                            return [
                                'title' => 'Удаление формы сервиса',
                                'message' => sprintf('Вы действительно хотите удалить форму "%s"?', $_data->id)
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
    private function getFormsForm()
    {
        $routeResult = $this->request->getAttribute(RouteResult::class);
        $routeParams = $routeResult->getMatchedParams();
        $isNew = $routeResult->getMatchedRouteName() === $this->routeName('forms.create');

        $formTypes = new Collection\Collection([
            ['id' => Entity\SynapseServiceForm::FORM_ENTITY, 'label' => 'Форма объекта'],
            ['id' => Entity\SynapseServiceForm::FORM_HIDDEN_SELECTION, 'label' => 'Скрытое поле'],
            ['id' => Entity\SynapseServiceForm::FORM_MULTIPLE_SELECTION, 'label' => 'Поле выбора'],
        ]);

        $formFields = [
            'name' => [
                'type' => \Qore\Form\Field\Text::class,
                'label'=> 'Название*',
                'placeholder' => 'Введите название формы',
                'info' => 'название должно быть уникальным в пределах данного синапса и состоять только из латинских символов нижнего/верхнего регистра, а также цифр',
                'validators' => [
                    [
                        'type' => \Qore\Form\Validator\Required::class,
                        'message' => 'Поле название обязательно.',
                        'break' => true,
                    ],
                    [
                        'type' => \Qore\Form\Validator\Callback::class,
                        'message' => 'Такая форма уже существует',
                        'options' => [
                            'callback' => function($_value, $_isNew, $_id) {
                                $mm = Qore::service(\Qore\ORM\ModelManager::class);

                                if ($_isNew) {
                                    $iSynapseService = $_id;
                                } else {
                                    $form = $mm('QSynapse:SynapseServiceForms')
                                        ->where(function($_where) use ($_value, $_id){
                                            $_where(['@this.id' => $_id]);
                                        })->one();
                                    $iSynapseService = $form['iSynapseService'];
                                }

                                $form = $mm('QSynapse:SynapseServiceForms')
                                    ->where(function($_where) use ($_value, $iSynapseService){
                                        $_where([
                                            '@this.name' => $_value,
                                            '@this.iSynapseService' => $iSynapseService,
                                        ]);
                                    })->one();

                                if ($_isNew) {
                                    return $form ? false : true;
                                } else {
                                    return is_null($form) || (int)$form->id == (int)$_id;
                                }
                            },
                            'callbackOptions' => [
                                '_isNew' => $isNew,
                                '_id' => $isNew ? $routeParams['service-id'] : $routeParams['id']
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
                        'message' => 'Обязательно укажите ярлык сервиса.',
                        'break' => true,
                    ],
                    [
                        'type' => \Qore\Form\Validator\Regex::class,
                        'options' => [
                            'pattern' => '/^[A-zА-я0-9 \-\/]+$/u'
                        ],
                        'message' => 'Неправильно заполнено поле ([A-zА-я0-9 -])',
                        'break' => true,
                    ],
                ]
            ],
            'type' => [
                'type' => \Qore\Form\Field\Select::class,
                'label'=> 'Тип формы',
                'validators' => [
                    [
                        'type' => \Qore\Form\Validator\Required::class,
                        'message' => 'Выберите тип',
                        'break' => true,
                    ],
                    [
                        'type' => \Qore\Form\Validator\InArray::class,
                        'message' => 'Неверный тип формы',
                        'options' => [
                            'haystack' => $formTypes->map(function($_type){
                                return $_type['id'];
                            })->toArray()
                        ],
                        'break' => false,
                    ],
                ],
                'options' => $formTypes->toArray(),
                'additional' => []
            ],
            'template' => [
                'type' => \Qore\Form\Field\Text::class,
                'label'=> 'Шаблон списка',
                'placeholder' => 'Укажите шабон для списка',
                'info' => 'шаблон представления элементов списка в поле выбора, используйте "$" для обозначения названия атрибута.',
                'conditions' => [
                    // new \Qore\Form\Condition\Compare('type', Entity\SynapseServiceForm::FORM_MULTIPLE_SELECTION)
                ]
            ],
            'description' => [
                'type' => \Qore\Form\Field\Textarea::class,
                'label'=> 'Описание',
                'placeholder' => 'Описание',
                'validators' => []
            ],
            'submit' => [
                'type' => \Qore\Form\Field\Submit::class,
                'label' => 'Создать',
            ],
        ];

        return Qore::service(\Qore\Form\FormManager::class)(
            # - Form name
            'service-form',
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
