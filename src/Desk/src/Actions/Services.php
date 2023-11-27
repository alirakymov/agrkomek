<?php

namespace Qore\Desk\Actions;


use Qore\InterfaceGateway\Component\Table;
use Qore\InterfaceGateway\InterfaceGateway;
use Qore\Qore;
use Qore\Daemon\Supervisor\SupervisorConfigurator;
use Qore\Front as QoreFront;
use Qore\Router\RouteCollector;
use Qore\Desk\Actions\BaseAction;
use Qore\Daemon\Supervisor;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Mezzio\Helper\UrlHelper;
use Mezzio\Router\RouteResult;
use Qore\InterfaceGateway\Component\Modal;

/**
 * Class: Services
 *
 * @see BaseAction
 */
class Services extends BaseAction
{
    /**
     * Return routes array
     *
     * @return array
     */
    public function routes(RouteCollector $_router) : void
    {
        $_router->group('/service', null, function ($_router) {
            $_router->any('[/{id:[0-9]+}]', 'index');
            $_router->any('/inspect', 'inspect');
            $_router->any('/start[/{id:[0-9]+}]', 'start');
            $_router->any('/stop[/{id:[0-9]+}]', 'stop');
            $_router->any('/restart[/{id:[0-9]+}]', 'restart');
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
            case $routeResult->getMatchedRouteName() === $this->routeName('inspect'):
                return $this->runInspect();
            case $routeResult->getMatchedRouteName() === $this->routeName('start'):
                return $this->runStart();
            case $routeResult->getMatchedRouteName() === $this->routeName('stop'):
                return $this->runStop();
            case $routeResult->getMatchedRouteName() === $this->routeName('restart'):
                return $this->runRestart();
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

        $launchedServices = Qore::collection(Qore::service(Supervisor\Supervisor::class)->getAllProcessInfo());

        $mm = Qore::service(\Qore\ORM\ModelManager::class);

        $columns = [
            'id' => [
                'label' => '#',
                'model-path' => 'id',
                'class-header' => 'col-sm-1 text-center',
                'class-column' => 'col-sm-1 text-center',
            ],
            'name' => [
                'label' => 'Сервис',
                'class-header' => 'col-sm-3',
            ],
            'status' => [
                'label' => 'Статус',
                'class-header' => 'col-sm-1',
                'transform' => function ($_item) use ($launchedServices) {
                    $service = $launchedServices->firstMatch(['name' => $_item->name()]);
                    return [
                        'isLabel' => true,
                        'class' => ! is_null($service) && $service['statename'] == 'RUNNING' ? 'label-success' : 'label-warning',
                        'label' => isset($service['statename']) ? $service['statename'] : 'INACTIVE',
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
                    'start' => [
                        'label' => 'Запустить',
                        'icon' => 'fa fa-play',
                        'actionUri' => function($_data) {
                            return Qore::service(UrlHelper::class)->generate($this->routeName('start'), ['id' => $_data->id]);
                        },
                    ],
                    'stop' => [
                        'label' => 'Остановить',
                        'icon' => 'fa fa-stop',
                        'actionUri' => function($_data) {
                            return Qore::service(UrlHelper::class)->generate($this->routeName('stop'), ['id' => $_data->id]);
                        },
                    ],
                    'restart' => [
                        'label' => 'Перезапустить',
                        'icon' => 'fas fa-sync-alt',
                        'actionUri' => function($_data) {
                            return Qore::service(UrlHelper::class)->generate($this->routeName('restart'), ['id' => $_data->id]);
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
                                'title' => 'Удаление сервиса',
                                'message' => sprintf('Вы действительно хотите удалить сервис "%s"?', $_data->name)
                            ];
                        },
                    ],
                ],
            ],
        ];

        $componentActions = [
            'create' => [
                'icon' => 'fa fa-plus fa-fw',
                'actionUri' => Qore::service(UrlHelper::class)->generate($this->routeName('create'))
            ],
            'inspect' => [
                'icon' => 'fa fa-cogs fa-fw',
                'actionUri' => Qore::service(UrlHelper::class)->generate($this->routeName('inspect'))
            ],
        ];

        $ig = Qore::service(InterfaceGateway::class);

        $component = $ig(Table::class, 'services')
            ->setActions($componentActions)
            ->setTitle('Системные сервисы')
            ->setTableOptions([
                'url' => Qore::service(UrlHelper::class)->generate($this->routeName('index'))
            ])
            ->setTableData($columns, $this->getData());

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
     * getData
     *
     * @param int $_iParent
     */
    private function getData()
    {
        $mm = Qore::service(\Qore\ORM\ModelManager::class);
        return $mm('QSystem:Services')
            ->all();
    }

    /**
     * runRestart
     *
     */
    protected function runInspect()
    {
        $routeResult = $this->request->getAttribute(RouteResult::class, null);
        $routeParams = $routeResult->getMatchedParams();

        $supervisor = Qore::service(Supervisor\Supervisor::class);
        $configurator = Qore::service(SupervisorConfigurator::class);
        $mm = Qore::service('mm');

        $configurator->clear();

        $services = $mm('QSystem:Services')->all();
        foreach ($services as $service) {
            $configurator->build($service);
        }

        $ig = Qore::service(InterfaceGateway::class);
        # - Генерируем ответ
        return QoreFront\ResponseGenerator::get(
            $ig('services')
                ->run('reload')
        );

    }

    /**
     * runRestart
     *
     */
    protected function runRestart()
    {
        $routeResult = $this->request->getAttribute(RouteResult::class, null);
        $routeParams = $routeResult->getMatchedParams();

        $supervisor = Qore::service(Supervisor\Supervisor::class);

        $mm = Qore::service('mm');
        $service = $mm('QSystem:Services')->where(function($_where) use ($routeParams){
            $_where(['id' => $routeParams['id']]);
        })->one();

        if ($service) {
            $process = $supervisor->getProcess($service->name());
            if ($process->isRunning()) {
                $supervisor->stopProcess($service->name(), true);
            }
            $supervisor->startProcess($service->name(), false);
        }

        $ig = Qore::service(InterfaceGateway::class);
        # - Генерируем ответ
        return QoreFront\ResponseGenerator::get(
            $ig('services')
                ->run('reload')
        );

    }

    /**
     * runStop
     *
     */
    protected function runStop()
    {
        $routeResult = $this->request->getAttribute(RouteResult::class, null);
        $routeParams = $routeResult->getMatchedParams();

        $supervisor = Qore::service(Supervisor\Supervisor::class);

        $mm = Qore::service('mm');
        $service = $mm('QSystem:Services')->where(function($_where) use ($routeParams){
            $_where(['id' => $routeParams['id']]);
        })->one();

        if ($service) {
            $process = $supervisor->getProcess($service->name());
            if ($process->isRunning()) {
                $supervisor->stopProcess($service->name(), true);
            }
        }

        $ig = Qore::service(InterfaceGateway::class);
        # - Генерируем ответ
        return QoreFront\ResponseGenerator::get(
            $ig('services')
                ->run('reload')
        );

    }

    /**
     * runStart
     *
     */
    protected function runStart()
    {
        $routeResult = $this->request->getAttribute(RouteResult::class, null);
        $routeParams = $routeResult->getMatchedParams();

        $supervisor = Qore::service(Supervisor\Supervisor::class);

        $mm = Qore::service('mm');
        $service = $mm('QSystem:Services')->where(function($_where) use ($routeParams){
            $_where(['id' => $routeParams['id']]);
        })->one();

        if ($service) {
            $process = $supervisor->getProcess($service->name());
            if (! $process->isRunning()) {
                $supervisor->startProcess($service->name(), false);
            }
        }

        $ig = Qore::service(InterfaceGateway::class);
        # - Генерируем ответ
        return QoreFront\ResponseGenerator::get(
            $ig('services')
                ->run('reload')
        );

    }

    /**
     * runCreate
     *
     */
    protected function runCreate()
    {
        $routeResult = $this->request->getAttribute(RouteResult::class, null);
        $routeParams = $routeResult->getMatchedParams();

        $ig = Qore::service(InterfaceGateway::class);
        $modal = $ig(Modal::class, 'create-service');

        $fm = $this->getForm();

        if ($this->request->getMethod() === 'POST') {
            $fm->setData($this->request->getParsedBody());
            if ($fm->isValid()) {
                # - Save form data through Qore\ORM
                ($mm = Qore::service(\Qore\ORM\ModelManager::class))(
                    $mm('QSystem:Services', $fm->getData())
                )->save();

                return QoreFront\ResponseGenerator::get(
                    # - Command: modal close
                    $modal->run('close'),
                    # - Command: reload table
                    $ig(Table::class, 'services')
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
                $this->getFrontProtocol()
                    ->component(
                        $modal->setTitle('Создание нового сервиса')
                            ->component(Qore::service(\Qore\Form\Decorator\QoreFront::class)->decorate($fm))
                            ->run('open')
                    ),
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

        $mm = Qore::service(\Qore\ORM\ModelManager::class);
        $result = $mm('QSystem:Services')
            ->where(function($_where) use ($routeParams) {
                $_where(['@this.id' => $routeParams['id']]);
            })
            ->one();

        $ig = Qore::service(InterfaceGateway::class);

        if ($this->request->getMethod() === 'POST') {

            $fm->setData($this->request->getParsedBody());

            if ($fm->isValid()) {
                $data = $fm->getData();
                # - Save form data through Qore\ORM
                $mm($result->combine($data))->save();

                return QoreFront\ResponseGenerator::get(
                    # - Command: modal close
                    $ig('update-service')
                        ->run('close'),
                    # - Command: reload table
                    $ig('services')
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

            $fm->setData([
                'iParent' => $routeParams['id'] ?? 0,
            ]);

            $fm->setData($result);

            return QoreFront\ResponseGenerator::get(
                $this->getFrontProtocol()
                    ->component(
                        $ig(Modal::class, 'update-service')
                            ->setTitle('Редактирование сервиса')
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
            'name' => [
                'type' => \Qore\Form\Field\Text::class,
                'label'=> 'Название*',
                'placeholder' => 'Введите название сервиса',
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
            'autostart' => [
                'type' => \Qore\Form\Field\Switcher::class,
                'label'=> 'Автозапуск сервиса',
                'placeholder' => '',
                'info' => 'автозапуск сервиса при старте системы'
            ],
            'autorestart' => [
                'type' => \Qore\Form\Field\Switcher::class,
                'label'=> 'Перезапуск сервиса',
                'placeholder' => '',
                'info' => 'перезапуск сервиса при его внештатном завершении'
            ],
            'command' => [
                'type' => \Qore\Form\Field\Textarea::class,
                'label'=> 'Команда',
                'placeholder' => 'Введите команду сервиса',
                'info' => 'исполняемая команда сервиса',
                'validators' => [
                    [
                        'type' => \Qore\Form\Validator\Required::class,
                        'message' => 'Поле "Команда" обязательно.',
                        'break' => false,
                    ],
                ]
            ],
            'description' => [
                'type' => \Qore\Form\Field\Textarea::class,
                'label'=> 'Описание',
                'placeholder' => 'Введите описание сервиса',
                'info' => 'используется только в информативных целях',
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

    /**
     * runDelete
     *
     */
    protected function runDelete()
    {
        $routeResult = $this->request->getAttribute(RouteResult::class, null);
        $routeParams = $routeResult->getMatchedParams();

        ($mm = Qore::service(\Qore\ORM\ModelManager::class))(
            $mm('QSystem:Services', ['id' => $routeParams['id']])
        )->delete();

        return QoreFront\ResponseGenerator::get(
            QoreFront\Protocol\Component\QCTable::get('services')
                ->run('reload')
        );
    }

}
