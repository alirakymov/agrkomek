<?php

namespace Qore\Desk\Actions;


use Qore\Qore;
use Qore\Front as QoreFront;
use Qore\Router\RouteCollector;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Mezzio\Helper\UrlHelper;
use Mezzio\Router\RouteResult;

class Users extends BaseAction
{
    /**
     * Return routes array
     *
     * @return array
     */
    public function routes(RouteCollector $_router) : void
    {
        $_router->group('/users', null, function ($_router) {
            $_router->any('', 'index');
            $_router->any('/reload', 'reload');
            $_router->any('/create', 'create');
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
            case $routeResult->getMatchedRouteName() === $this->routeName('reload'):
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
        $em = Qore::service(\Qore\EventManager\EventManager::class);

        $columns = [
            'id' => [
                'label' => '#',
                'model-path' => 'id',
                'class-header' => 'col-sm-1 text-center',
                'class-column' => 'col-sm-1 text-center',
            ],
            'email' => [
                'label' => 'Email',
                'class-header' => 'col-sm-3',
            ],
            'username' => [
                'label' => 'Пользователь',
                'class-header' => 'col-sm-4',
                'transform' => function(\Qore\Core\Entities\QSystemUser $_user) {
                    return $_user->lastName . ' ' . $_user->firstName;
                }
            ],
            'privilege' => [
                'label' => 'Привилегия',
                'class-header' => 'col-sm-3',
                'transform' => function(\Qore\Core\Entities\QSystemUser $_user) {
                    switch ((int)$_user->privilege) {
                        case 1:
                            return 'Администратор';
                        case 10:
                            return 'Менеджер';
                        case 20:
                            return 'Оператор';
                    }
                },
            ],
            'table-actions' => [
                'label' => 'Действия',
                'class-header' => 'col-sm-1',
                'class-column' => 'col-sm-1 text-center',
                'actions' => [
                    'update' => [
                        'label' => 'Редактировать',
                        'icon' => 'fa fa-pencil',
                        'actionUri' => function($_user) {
                            return Qore::service(UrlHelper::class)->generate($this->routeName('update'), ['id' => $_user->id]);
                        },
                    ],
                    'delete' => [
                        'label' => 'Удалить',
                        'icon' => 'fa fa-trash',
                        'actionUri' => function($_user) {
                            return Qore::service(UrlHelper::class)->generate($this->routeName('delete'), ['id' => $_user->id]);
                        },
                        'confirm' => function($_user) {
                            return [
                                'title' => 'Удаление пользователя',
                                'message' => sprintf('Вы действительно хотите удалить пользователя "%s, %s"?', $_user->lastName, $_user->firstName)
                            ];
                        },
                    ],
                ],
            ]
        ];

        $componentActions = [
            'create' => [
                'icon' => 'fa fa-plus',
                'actionUri' => Qore::service(UrlHelper::class)->generate($this->routeName('create'))
            ]
        ];

        $usersComponent = QoreFront\Protocol\Component\QCTable::get('users')
            ->setActions($componentActions)
            ->setTableOptions([
                'url' => Qore::service(UrlHelper::class)->generate($this->routeName('index'))
            ])
            ->setTableData($columns, $this->getUsers())
            ->setTitle('Пользователи');

        if ($this->request->isXmlHttpRequest()) {

            return QoreFront\ResponseGenerator::get($usersComponent);

        } else {

            $frontProtocol = $this->getFrontProtocol()
                ->component($usersComponent->inBlock(true));

            return new HtmlResponse($this->template->render('app::main', [
                'title' => 'Qore.CRM',
                'frontProtocol' => $frontProtocol->asArray(),
            ]));
        }
    }

    /**
     * getUsers
     *
     */
    private function getUsers()
    {
        $mm = Qore::service(\Qore\ORM\ModelManager::class);
        $result = $mm('QSystem:Users')->all();
        return $mm('QSystem:Users')->all();
    }

    /**
     * runCreate
     *
     */
    protected function runCreate()
    {
        $fm = $this->getForm();

        if ($this->request->getMethod() === 'POST') {

            $fm->setData($this->request->getParsedBody());

            if ($fm->isValid()) {
                # - Save form data through Qore\ORM
                ($mm = Qore::service(\Qore\ORM\ModelManager::class))(
                    $mm('QSystem:Users', $fm->getData())
                )->save();

                return QoreFront\ResponseGenerator::get(
                    # - Command: modal close
                    QoreFront\Protocol\Component\QCModal::get('create-user')
                        ->run('close'),
                    # - Command: reload table
                    QoreFront\Protocol\Component\QCTable::get('users')
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

            return QoreFront\ResponseGenerator::get(
                $this->getFrontProtocol()
                    ->component(
                        QoreFront\Protocol\Component\QCModal::get('create-user')
                            ->setTitle('Создание нового пользователя')
                            ->component(Qore::service(\Qore\Form\Decorator\QoreFront::class)->decorate($fm))
                    ),
                QoreFront\Protocol\Component\QCModal::get('create-user')
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
                if ($data['password'] === '') {
                    unset($data['password']);
                }

                # - Save form data through Qore\ORM
                ($mm = Qore::service(\Qore\ORM\ModelManager::class))(
                    $mm('QSystem:Users', array_merge($data, ['id' => $routeParams['id']]))
                )->save();

                return QoreFront\ResponseGenerator::get(
                    # - Command: modal close
                    QoreFront\Protocol\Component\QCModal::get('create-user')
                        ->run('close'),
                    # - Command: reload table
                    QoreFront\Protocol\Component\QCTable::get('users')
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
            $result = $mm('QSystem:Users')
                ->where(function($_where) use ($routeParams) {
                    $_where(['@this.id' => $routeParams['id']]);
                })
                ->one();

            $fm->setData($result);

            return QoreFront\ResponseGenerator::get(
                $this->getFrontProtocol()
                    ->component(
                        QoreFront\Protocol\Component\QCModal::get('create-user')
                            ->setTitle('Редактирование пользователя')
                            ->component(Qore::service(\Qore\Form\Decorator\QoreFront::class)->decorate($fm))
                    ),
                QoreFront\Protocol\Component\QCModal::get('create-user')
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

        $passwordValidators = [
            [
                'type' => \Qore\Form\Validator\Length::class,
                'message' => 'Пароль должен состоять минимум из %min% символов',
                'options' => [
                    'min' => 8,
                ],
            ],
        ];

        if ($routeResult->getMatchedRouteName() !== $this->routeName('update')) {
            $passwordValidators = array_merge([
                [
                    'type' => \Qore\Form\Validator\Required::class,
                    'message' => 'Необходимо указать пароль.',
                    'break' => true,
                ],
            ], $passwordValidators);
        }

        $formFields = [
            'email' => [
                'type' => \Qore\Form\Field\Email::class,
                'label'=> 'Email*',
                'placeholder' => 'Введите эл. адрес',
                'info' => 'на данный адрес будет выслано письмо для подтверждения',
                'validators' => [
                    [
                        'type' => \Qore\Form\Validator\Required::class,
                        'message' => 'Поле email обязательно.',
                        'break' => false,
                    ],
                    [
                        'type' => \Qore\Form\Validator\EmailAddress::class,
                        'message' => 'Неправильно заполнено поле',
                        'break' => true,
                    ],
                ]
            ],
            'password' => [
                'type' => \Qore\Form\Field\Password::class,
                'label'=> 'Пароль',
                'placeholder' => 'Введите пароль',
                'validators' => $passwordValidators,
            ],
            'lastName' => [
                'type' => \Qore\Form\Field\Text::class,
                'label'=> 'Фамилия',
                'placeholder' => 'Фамилия',
                'validators' => [
                    [
                        'type' => \Qore\Form\Validator\Required::class,
                        'message' => 'Поле "Фамилия" обязательное',
                        'break' => true,
                    ],
                ]
            ],
            'firstName' => [
                'type' => \Qore\Form\Field\Text::class,
                'label'=> 'Имя',
                'placeholder' => 'Имя',
                'validators' => [
                    [
                        'type' => \Qore\Form\Validator\Required::class,
                        'message' => 'Поле "Имя" обязательное',
                        'break' => true,
                    ],
                ]
            ],
            'submit' => [
                'type' => \Qore\Form\Field\Submit::class,
                'label' => 'Создать',
            ],
        ];

        return Qore::service(\Qore\Form\FormManager::class)(
            # - Form name
            'new-user',
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
        Qore::service('debug')->message($this->routeName('delete'));

        $routeResult = $this->request->getAttribute(RouteResult::class, null);
        $routeParams = $routeResult->getMatchedParams();

        ($mm = Qore::service(\Qore\ORM\ModelManager::class))(
            $mm('QSystem:Users', ['id' => $routeParams['id']])
        )->delete();

        return QoreFront\ResponseGenerator::get(
            QoreFront\Protocol\Component\QCTable::get('users')
                ->run('reload')
        );
    }

}
