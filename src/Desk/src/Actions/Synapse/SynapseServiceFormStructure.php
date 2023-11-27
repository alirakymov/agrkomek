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
 * Class: SynapseServiceFormStructure
 *
 * @see BaseAction
 */
class SynapseServiceFormStructure extends BaseAction
{
    /**
     * Return routes array
     *
     * @return array
     */
    public function routes(RouteCollector $_router) : void
    {
        $_router->group('/service-service-form-structure', null, function($_router) {
            $_router->any('/{form-id:[0-9]+}', 'index');
            $_router->group('/fields', 'fields', function($_router) {
                $_router->any('/reload/{form-id:[0-9]+}', 'reload');
                $_router->any('/reorder/{form-id:[0-9]+}', 'reorder');
                $_router->any('/create/{form-id:[0-9]+}', 'create');
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
            })->implode();

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

        if (! $form = $this->getForm($routeParams['form-id'])) {
            return new JsonResponse([]);
        }

        $ig = Qore::service(InterfaceGateway::class);
        $tabsComponent = $ig(Tabs::class, 'service-form-structure')
            ->setTitle(sprintf('Структура формы "%s" сервиса "%s"', $form->name, $form->service->name))
            ->tab('form-fields', function($_tab) use ($form) {
                $_tab->setLabel('Поля формы')->component($this->getFieldsComponent($form->id));
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
     * getForm
     *
     * @param mixed $_id
     */
    private function getForm($_id)
    {
        $mm = Qore::service(\Qore\ORM\ModelManager::class);
        return $mm('QSynapse:SynapseServiceForms')
            ->with('service')
            ->where(function($_where) use ($_id){
                $_where(['@this.id' => $_id]);
            })
            ->one();
    }

    /**
     * runFieldsReload
     *
     */
    private function runFieldsReload()
    {
        $routeResult = $this->request->getAttribute(RouteResult::class, null);
        $routeParams = $routeResult->getMatchedParams();

        return QoreFront\ResponseGenerator::get($this->getFieldsComponent($routeParams['form-id']));
    }

    /**
     * runFieldsReorder
     *
     */
    private function runFieldsReorder()
    {
        $routeResult = $this->request->getAttribute(RouteResult::class, null);
        $routeParams = $routeResult->getMatchedParams();

        $mm = Qore::service('mm');
        $form = $mm('QSynapse:SynapseServiceForms')->where(function($_where) use ($routeParams){
            $_where(['@this.id' => $routeParams['form-id']]);
        })->one();

        if ($form) {
            $form['__options'] = array_merge($form['__options'] ?: [], [
                'fields-order' => $this->request->getParsedBody()['data'],
            ]);
            $mm($form)->save();
        }
        return QoreFront\ResponseGenerator::get();
    }

    /**
     * runFieldsCreate
     *
     */
    private function runFieldsCreate()
    {
        $routeResult = $this->request->getAttribute(RouteResult::class, null);
        $routeParams = $routeResult->getMatchedParams();

        if (! isset($routeParams['form-id'])) {
            return new JsonResponse([]);
        }

        $fm = $this->getFieldsForm();
        $ig = Qore::service(InterfaceGateway::class);
        $modal = $ig(Modal::class, 'create-service-form-field');

        if ($this->request->getMethod() === 'POST') {

            $fm->setData($this->request->getParsedBody());

            if ($fm->isValid()) {
                # - Save form data through Qore\ORM
                ($mm = Qore::service(\Qore\ORM\ModelManager::class))(
                    $mm('QSynapse:SynapseServiceFormFields', array_merge($fm->getData(), ['iSynapseServiceForm' => $routeParams['form-id']]))
                )->save();

                return QoreFront\ResponseGenerator::get(
                    # - Command: modal close
                    $modal->run('close'),
                    # - Command: reload table
                    $ig('service-form-fields')->run('reload')
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
                        $modal->setTitle('Добавление поля к форме')
                            ->component(Qore::service(\Qore\Form\Decorator\QoreFront::class)->decorate($fm))
                            ->run('open')
                    )
            );
        }
    }

    /**
     * runFieldsUpdate
     *
     */
    private function runFieldsUpdate()
    {
        $routeResult = $this->request->getAttribute(RouteResult::class, null);
        $routeParams = $routeResult->getMatchedParams();

        $mm = Qore::service(\Qore\ORM\ModelManager::class);

        $result = $mm('QSynapse:SynapseServiceFormFields')
            ->where(function($_where) use ($routeParams) {
                $_where(['@this.id' => $routeParams['id']]);
            })
            ->one();

        $fm = $this->getFieldsForm();
        $ig = Qore::service(InterfaceGateway::class);
        $modal = $ig(Modal::class, 'update-service-form-field');

        if ($this->request->getMethod() === 'POST') {

            $fm->setData($this->request->getParsedBody());

            if ($fm->isValid()) {

                # - Save form data through Qore\ORM
                $mm($result->combine($fm->getData()))->save();

                return QoreFront\ResponseGenerator::get(
                    # - Command: modal close
                    $modal->run('close'),
                    # - Command: reload table
                    $ig('service-form-fields')->run('reload')
                );

            } else {
                # - Generate response for form
                return QoreFront\ResponseGenerator::get(
                    # - Command: update form
                    Qore::service(\Qore\Form\Decorator\QoreFront::class)->decorate($fm)
                );
            }

        } else {

            $fm->setData($result->prepareDataToForm());
            return QoreFront\ResponseGenerator::get(
                $this->getFrontProtocol()
                    ->component(
                        $modal->setTitle('Редактирование субъекта сервиса')
                            ->component(Qore::service(\Qore\Form\Decorator\QoreFront::class)->decorate($fm))
                            ->run('open')
                    )
            );
        }
    }

    /**
     * runFieldsDelete
     *
     */
    private function runFieldsDelete()
    {
        $routeResult = $this->request->getAttribute(RouteResult::class, null);
        $routeParams = $routeResult->getMatchedParams();

        ($mm = Qore::service(\Qore\ORM\ModelManager::class))(
            $mm('QSynapse:SynapseServiceFormFields', ['id' => $routeParams['id']])
        )->delete();

        $ig = Qore::service(InterfaceGateway::class);
        return QoreFront\ResponseGenerator::get(
            $ig('service-form-fields')->run('reload')
        );
    }

    /**
     * getFieldsList
     *
     */
    private function getFieldsComponent($_formId)
    {
        $routeResult = $this->request->getAttribute(RouteResult::class, null);
        $routeParams = $routeResult->getMatchedParams();

        $ig = Qore::service(InterfaceGateway::class);

        return $ig(Table::class, 'service-form-fields')
            ->setActions([
                'create' => [
                    'icon' => 'fa fa-plus',
                    'actionUri' => Qore::service(UrlHelper::class)->generate($this->routeName('fields.create'), $routeParams)
                ]
            ])->setTableOptions([
                'url' => Qore::service(UrlHelper::class)->generate($this->routeName('fields.reload'), $routeParams),
                'sortable' => Qore::service(UrlHelper::class)->generate($this->routeName('fields.reorder'), $routeParams),

            ])
            ->setTableData($this->getFieldsColumns(), $this->getFields($_formId));
    }

    /**
     * getFields
     *
     * @param mixed $_iForm
     */
    private function getFields($_iForm)
    {
        $mm = Qore::service(\Qore\ORM\ModelManager::class);
        $fields = $mm('QSynapse:SynapseServiceFormFields')
            ->with('relatedForm', function($_gw){
                $_gw->with('service', function($_gw){
                    $_gw->with('synapse');
                });
            })
            ->with('relatedAttribute')
            ->where(function($_where) use ($_iForm){
                $_where(['@this.iSynapseServiceForm' => $_iForm])
                    ->AND(function($_where){
                        $_where->isNotNull('@this.relatedForm.id')->or->isNotNull('@this.relatedAttribute.id');
                    });
            })
            ->all();

        $form = $this->getForm($_iForm);
        if (isset($form['__options']['fields-order'])) {
            $fieldsOrder = array_values($form['__options']['fields-order']);
            $fields = $fields->sortBy(function($_item) use ($fieldsOrder) {
                return (int)array_search($_item->id, $fieldsOrder);
            }, SORT_ASC);
        }

        return $fields;
    }

    /**
     * getFieldsColumns
     *
     */
    private function getFieldsColumns()
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
            'type' => [
                'label' => 'Тип',
                'class-header' => 'col-sm-2',
                'transform' => function($_item) {
                    return $_item['type'] == Entity\SynapseServiceFormField::IS_ATTRIBUTE
                        ? 'Атрибут'
                        : 'Субъект';
                }
            ],
            'object' => [
                'label' => 'Объект',
                'class-header' => 'col-sm-2',
                'transform' => function($_item) {
                    if ($_item['type'] == Entity\SynapseServiceFormField::IS_ATTRIBUTE) {
                        return $_item['relatedAttribute']->label . ' (' . $_item['relatedAttribute']->name . ')';
                    } else {
                        return $_item->relatedForm->label . ' (' . $_item->relatedForm->service->synapse->name . '::' . $_item->relatedForm->service->name . ')';
                    }
                }
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
                            return Qore::service(UrlHelper::class)->generate($this->routeName('fields.update'), ['id' => $_data->id]);
                        },
                    ],
                    'delete' => [
                        'label' => 'Удалить',
                        'icon' => 'fa fa-trash',
                        'actionUri' => function($_data) {
                            return Qore::service(UrlHelper::class)->generate($this->routeName('fields.delete'), ['id' => $_data->id]);
                        },
                        'confirm' => function($_data) {
                            return [
                                'title' => 'Удаление субъекта сервиса',
                                // 'message' => sprintf('Вы действительно хотите удалить субъект "%s:%s"?', $_data->serviceTo->synapse->name, $_data->serviceTo->name)
                                'message' => sprintf('Вы действительно хотите удалить субъект "%s:%s"?', 'test', 'test')
                            ];
                        },
                    ],
                ],
            ]
        ];
    }

    /**
     * getFieldsForm
     *
     */
    private function getFieldsForm()
    {
        $routeResult = $this->request->getAttribute(RouteResult::class);
        $routeParams = $routeResult->getMatchedParams();
        $isNew = $routeResult->getMatchedRouteName() === $this->routeName('fields.create');

        $mm = Qore::service(\Qore\ORM\ModelManager::class);

        if ($isNew) {
            $form = $mm('QSynapse:SynapseServiceForms')
                ->with('service', function($_gw) {
                    $_gw->with('subjectsFrom', function($_gw) {
                        $_gw->with('serviceTo', function($_gw) {
                            $_gw->with('synapse');
                            $_gw->with('forms');
                        });
                    })->with('synapse', function($_gw) {
                        $_gw->with('attributes');
                    });
                })
                ->where(function($_where) use ($routeParams) {
                    $_where(['@this.id' => $routeParams['form-id']]);
                })
                ->one();
        } else {
            $form = $mm('QSynapse:SynapseServiceForms')
                ->with('service', function($_gw) { # - Get Service with Subjects
                    $_gw->with('subjectsFrom', function($_gw) {
                        $_gw->with('serviceTo', function($_gw) { # - Get related Service with Forms
                            $_gw->with('synapse');
                            $_gw->with('forms');
                        });
                    })->with('synapse', function($_gw) { # - Get Synapse With Attributes
                        $_gw->with('attributes');
                    });
                })
                ->with('fields', function($_gw) use ($routeParams) { # - Filter by field id
                    $_gw->where(function($_where) use ($routeParams) {
                        $_where(['@this.id' => $routeParams['id']]);
                    });
                })
                ->one();
        }

        # - Собираем все текущие поля формы
        $formSubjectFields = $mm('QSynapse:SynapseServiceFormFields')
            ->with('relatedForm', function ($_gw) {
                $_gw->with('service');
            })
            ->with('relatedAttribute')
            ->where(function($_where) use ($form) {
                $_where([ '@this.iSynapseServiceForm' => $form->id ]);
            })
            ->all();

        # - Собираем субъекты уже реализованные в других полях
        $usedServiceSubjects = $formSubjectFields
            ? $formSubjectFields
                ->filter(function($field) use ($isNew, $routeParams) {
                    // - Если редактируем субъект, то собираем всех кроме текущего
                    return ($isNew || (int)$field->id != (int)$routeParams['id'])
                        && (int)$field->type == Entity\SynapseServiceFormField::IS_SUBJECT
                        && $field->relatedForm;
                })
                ->map(function($field, $key) {
                    return $field->relatedForm->service->id;
                })->toArray(false)
            : [];

        $serviceSubjectOptions = $form->service->subjectsFrom
            ->filter(function($subject) use ($usedServiceSubjects) {
                return ! in_array($subject->serviceTo->id, $usedServiceSubjects);
            })
            ->map(function($subject) {
                return [
                    'id' => 's' . $subject->serviceTo->id,
                    'label' => $subject->serviceTo->synapse->name . ':' . $subject->serviceTo->name,
                    'children' => $subject->serviceTo->forms
                        ->map(function ($form) use ($subject) {
                            return [
                                'id' => $subject->id . ':' . $form->id,
                                'label' => $form->label . ' (' . $form->name . ')',
                            ];
                        })->toArray(false),
                ];
            });

        # - Собираем субъекты уже реализованные в других полях
        $usedServiceAttributes = $formSubjectFields
            ? $formSubjectFields
                ->filter(function($field) use ($isNew, $routeParams) {
                    // - Если редактируем субъект, то собираем всех кроме текущего
                    return ($isNew || (int)$field->id != (int)$routeParams['id'])
                        && (int)$field->type == Entity\SynapseServiceFormField::IS_ATTRIBUTE
                        && ! is_null($field->relatedAttribute);
                })
                ->map(function($field, $key) {
                    return $field->relatedAttribute->id;
                })->toArray(false)
            : [];

        $serviceAttributeOptions = $form->service->synapse->attributes
            ->filter(function($attribute) use ($usedServiceAttributes) {
                return ! in_array($attribute->id, $usedServiceAttributes);
            })
            ->map(function($attribute) {
                return [
                    'id' => $attribute->id,
                    'label' => $attribute->name,
                ];
            });

        $fieldTypes = (new \Qore\Collection\Collection(Entity\SynapseServiceFormField::getTypes()))
            ->map(function($type, $field) {
                $types = [
                    'dropzone' => 'DropZone - область загрузки',
                    'email' => 'Email - поле ввода email',
                    'password' => 'Password - поле ввода пароля',
                    'select' => 'Select - выпадающий список',
                    'slider' => 'Slider - диапазонный слайдер',
                    'switcher' => 'Switcher - переключатель',
                    'text' => 'Text - текстовое поле',
                    'textarea' => 'Textarea - многострочное текстовое поле',
                    'wysiwyg' => 'Wysiwyg - визуальный редактор',
                    'treeselect' => 'TreeSelect - древовидный выпадающий список с множественным выбором',
                    'datetime' => 'Datetime - выбор даты',
                    'colorpicker' => 'Colorpicker - выбор цвета',
                    'codeeditor' => 'CodeEditor - редактор кода',
                ];

                return [
                    'id' => $type,
                    'label' => $types[$field] ?? $field,
                ];
            });

        $formFields = [
            'label' => [
                'type' => \Qore\Form\Field\Text::class,
                'label'=> 'Ярлык*',
                'placeholder' => 'Введите ярлык поля',
                'info' => 'будет использовано в качестве представления поля',
                'validators' => [
                ],
            ],
            'placeholder' => [
                'type' => \Qore\Form\Field\Text::class,
                'label'=> 'Приглашение к вводу',
                'placeholder' => 'Введите приглашение для ввода к этому полю',
                'info' => 'будет использовано в качестве атрибута placeholder поля',
                'validators' => [
                ],
            ],
            'type' => [
                'type' => \Qore\Form\Field\Select::class,
                'label'=> 'Тип поля',
                'placeholder' => 'Выберите тип поля',
                'info' => 'полем формы может быть или субъект сервиса или атрибут синапса',
                'options' => [
                    [
                        'id' => Entity\SynapseServiceFormField::IS_SUBJECT,
                        'label' => 'Субъект'
                    ],
                    [
                        'id' => Entity\SynapseServiceFormField::IS_ATTRIBUTE,
                        'label' => 'Атрибут'
                    ]
                ],
                'validators' => [
                    [
                        'type' => \Qore\Form\Validator\Required::class,
                        'message' => 'Выберите тип поля',
                        'break' => true,
                    ],
                    [
                        'type' => \Qore\Form\Validator\InArray::class,
                        'message' => 'Выбран неверный вариант типа',
                        'break' => false,
                        'options' => [
                            'haystack' => [
                                Entity\SynapseServiceFormField::IS_SUBJECT,
                                Entity\SynapseServiceFormField::IS_ATTRIBUTE
                            ],
                        ]
                    ],
                ]
            ],
            'relatedSynapseServiceSubject' => [
                'type' => \Qore\Form\Field\TreeSelect::class,
                'label'=> 'Целевая форма',
                'validators' => [],
                'placeholder' => 'Форма субъекта',
                'options' => $serviceSubjectOptions->toArray(false),
                'additional' => [
                    'multi' => false,
                    'disable-branch-nodes' => true,
                ],
                'conditions' => [
                    new \Qore\Form\Condition\Compare('type', Entity\SynapseServiceFormField::IS_SUBJECT)
                ]
            ],
            'iSynapseAttribute' => [
                'type' => \Qore\Form\Field\Select::class,
                'label'=> 'Атрибут синапса',
                'validators' => [],
                'placeholder' => 'Выберите атрибут синапса',
                'options' => $serviceAttributeOptions->toArray(false),
                'additional' => [
                    'multi' => false,
                    'disable-branch-nodes' => true,
                ],
                'conditions' => [
                    new \Qore\Form\Condition\Compare('type', Entity\SynapseServiceFormField::IS_ATTRIBUTE)
                ]
            ],
            'attributeFieldType' => [
                'type' => \Qore\Form\Field\Select::class,
                'label'=> 'Тип поля атрибута',
                'validators' => [],
                'placeholder' => 'Тип поля атрибута синапса',
                'options' => $fieldTypes->toArray(false),
                'additional' => [
                    'multi' => false,
                    'disable-branch-nodes' => true,
                ],
                'conditions' => [
                    new \Qore\Form\Condition\Compare('type', Entity\SynapseServiceFormField::IS_ATTRIBUTE)
                ]
            ],
            'description' => [
                'type' => \Qore\Form\Field\Textarea::class,
                'label'=> 'Описание',
                'placeholder' => 'Введите описание',
                'info' => 'будет использовано в качестве информации к данному полю',
                'validators' => []
            ],
            'submit' => [
                'type' => \Qore\Form\Field\Submit::class,
                'label' => 'Создать',
            ],
        ];

        return Qore::service(\Qore\Form\FormManager::class)(
            # - Form name
            'synapse-form-field',
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
