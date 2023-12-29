<?php

declare(strict_types=1);

namespace Qore\App\Actions;


use Mezzio\Router\RouteResult;
use Qore\Middleware\Action\BaseActionMiddleware;
use Qore\ORM\ModelManager;
use Qore\Qore;
use Qore\Front as QoreFront;
use Qore\Router\RouteCollector;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Helper\UrlHelper;
use Qore\App\SynapseNodes\Components\Moderator\Authentication\Adapter\AuthenticationInterface;
use Qore\Desk\NotifyHubs\SystemHub;
use Qore\Desk\NotifyHubs\SystemNotifyHub;
use Qore\InterfaceGateway\Component\Auth;
use Qore\InterfaceGateway\Component\Form;
use Qore\InterfaceGateway\InterfaceGateway;
use Qore\NotifyManager\NotifyManager;
use Qore\SessionManager\SessionManager;

class Login extends BaseActionMiddleware
{
    /**
     * routes
     *
     * @param RouteCollector $_router
     */
    public function routes(RouteCollector $_router) : void
    {
        $_router->any('/login', 'index');
        $_router->any('/register', 'register');
    }

    /**
     * process
     *
     * @param ServerRequestInterface $_request
     * @param DelegateInterface $_delegate
     */
    public function process(ServerRequestInterface $_request, RequestHandlerInterface $_handler) : ResponseInterface
    {
        /**@var RouteResult */
        $route = $_request->getAttribute(RouteResult::class);

        switch ($route->getMatchedRouteName()) {
            case $this->routeName('index'):
                return $this->login($_request, $_handler);
            case $this->routeName('register'):
                return $this->register($_request, $_handler);
        }
    }

    /**
     * Login action
     *
     * @param \Psr\Http\Message\ServerRequestInterface $_request 
     * @param \Psr\Http\Server\RequestHandlerInterface $_handler 
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    private function login(ServerRequestInterface $_request, RequestHandlerInterface $_handler): ResponseInterface
    {
        if ($_request->getMethod() === 'POST') {
            return $this->authenticate($_request);
        }

        $loginFields = [
            'email' => [
                'type' => 'text',
                'label'=> 'Email',
                'placeholder' => 'Введите эл. адрес',
                'info' => 'на данный адрес будет выслано письмо для подтверждения',
            ],
            'password' => [
                'type' => 'password',
                'label'=> 'Пароль',
                'placeholder' => 'Введите пароль',
            ],
            'submit' => [
                'type' => 'submit',
                'label' => 'Войти',
            ],
        ];

        $ig = Qore::service(InterfaceGateway::class);
        $component = $ig(Auth::class, 'auth-layout')
            ->component(
                $ig(Form::class, 'form-login')
                    ->setAction(Qore::service(UrlHelper::class)->generate($this->routeName('index')))
                    ->setFields($loginFields)
                    ->setModel($this->getAuthParams($_request))
            )->in('qore-app');

        return new HtmlResponse($this->template->render('app::login', [
            'title' => 'Авторизация',
            'frontProtocol' => $component->compose()
        ]));
    }

    /**
     * authenticate
     *
     * @param ServerRequestInterface $_request
     */
    public function authenticate(ServerRequestInterface $_request)
    {
        $authParams = $this->getAuthParams($_request);

        $errors = [];

        if (empty($authParams['email'])) {
            $errors['email'] = 'Введите ваш электронный адрес';
        }

        if (empty($authParams['password'])) {
            $errors['password'] = 'Введите пароль';
        }

        $ig = Qore::service(InterfaceGateway::class);

        if ($errors) {
            return new JsonResponse(
                $ig(Form::class, 'form-login')->setErrors($errors)->compose()
            );
        }

        $moderator = Qore::service(AuthenticationInterface::class)->authenticate($_request);

        if (! $moderator) {
            return new JsonResponse(
                $ig(Form::class, 'form-login')->setErrors([
                    'email' => 'Мы вас не нашли, пожалуйста перепроверьте данные.'
                ])->compose()
            );
        }

        return new JsonResponse($ig('auth-layout')->redirect(
            Qore::service(UrlHelper::class)->generate($this->routeName(ManagerIndex::class, 'index'))
        )->compose());
    }

    /**
     * Register action
     *
     * @param \Psr\Http\Message\ServerRequestInterface $_request 
     * @param \Psr\Http\Server\RequestHandlerInterface $_handler 
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    private function register(ServerRequestInterface $_request, RequestHandlerInterface $_handler): ResponseInterface
    {
        if ($_request->getMethod() === 'POST') {
            return $this->registerModerator($_request);
        }

        $registerFields = [
            'email' => [
                'type' => 'text',
                'label'=> 'Email',
                'placeholder' => 'Введите эл. адрес',
                'info' => 'Для входа в систему',
            ],
            'code' => [
                'type' => 'text',
                'label'=> 'Код',
                'placeholder' => 'Введите код',
            ],
            'password' => [
                'type' => 'password',
                'label'=> 'Пароль',
                'placeholder' => 'Введите пароль',
            ],
            'confirm-password' => [
                'type' => 'password',
                'label'=> 'Повторите пароль',
                'placeholder' => 'Повторите пароль',
            ],
            'firstname' => [
                'type' => 'text',
                'label'=> 'Имя',
                'placeholder' => 'Введите имя',
            ],
            'lastname' => [
                'type' => 'text',
                'label'=> 'Фамилия',
                'placeholder' => 'Введите фамилию',
            ],
            'submit' => [
                'type' => 'submit',
                'label' => 'Зарегистрироваться',
            ],
        ];

        $ig = Qore::service(InterfaceGateway::class);
        $component = $ig(Auth::class, 'auth-layout')
            ->component(
                $ig(Form::class, 'form-register')
                    ->setAction(Qore::service(UrlHelper::class)->generate($this->routeName('register')))
                    ->setFields($registerFields)
                    ->setModel($this->getAuthParams($_request))
            )->in('qore-app');

        return new HtmlResponse($this->template->render('app::login', [
            'title' => 'Авторизация',
            'frontProtocol' => $component->compose()
        ]));
    }

    /**
     * getAuthParams
     *
     * @param ServerRequestInterface $_request
     */
    private function getAuthParams(ServerRequestInterface $_request)
    {
        return array_merge([
            'email' => null,
            'password' => null,
        ], $_request->getParsedBody());
    }

    /**
     * Register 
     *
     * @param ServerRequestInterface $_request
     */
    public function registerModerator(ServerRequestInterface $_request)
    {
        $data = $_request->getParsedBody();

        $errors = [];

        if (empty($data['email'])) {
            $errors['email'] = 'Введите ваш электронный адрес';
        }

        if (empty($data['code'])) {
            $errors['code'] = 'Введите код';
        }

        if (empty($data['password'])) {
            $errors['password'] = 'Введите пароль';
        }

        if (mb_strlen($data['password']) < 8) {
            $errors['password'] = 'Пароль должен быть не менее 8 символов';
        }

        if (empty($data['confirm-password'])) {
            $errors['confirm-password'] = 'Повторите пароль';
        }

        if ($data['confirm-password'] !== $data['password']) {
            $errors['confirm-password'] = 'Пароли не совпадают';
        }

        if (empty($data['firstname'])) {
            $errors['firstname'] = 'Введите имя';
        }

        if (empty($data['lastname'])) {
            $errors['lastname'] = 'Введите фамилию';
        }

        $mm = Qore::service(ModelManager::class);
        $moderator = $mm('SM:Moderator')
            ->where(['@this.email' => $data['email'], '@this.otp' => $data['code']])
            ->one();

        if (is_null($moderator)) {
            $errors['email'] = 'Мы вас не нашли';
        }

        $ig = Qore::service(InterfaceGateway::class);

        if ($errors) {
            return new JsonResponse(
                $ig(Form::class, 'form-register')->setErrors($errors)->compose()
            );
        }

        $moderator->otp = null;
        $moderator->password = $data['password'];
        $moderator->firstname = $data['firstname'];
        $moderator->lastname = $data['lastname'];
        $mm($moderator)->save();

        $moderator = Qore::service(AuthenticationInterface::class)->authenticate($_request);

        return new JsonResponse($ig('auth-layout')->redirect(
            Qore::service(UrlHelper::class)->generate($this->routeName(ManagerIndex::class, 'index'))
        )->compose());
    }

}
