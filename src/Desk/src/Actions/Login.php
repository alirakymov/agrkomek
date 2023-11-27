<?php

declare(strict_types=1);

namespace Qore\Desk\Actions;


use Qore\InterfaceGateway\Component\Auth;
use Qore\InterfaceGateway\Component\Form;
use Qore\InterfaceGateway\InterfaceGateway;
use Qore\Middleware\Action\BaseActionMiddleware;
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
    }

    /**
     * process
     *
     * @param ServerRequestInterface $_request
     * @param DelegateInterface $_delegate
     */
    public function process(ServerRequestInterface $_request, RequestHandlerInterface $_handler) : ResponseInterface
    {

        /* {{{ - Test/Examples of ORM methods
            $mapper = new \Qore\ORM\Mapper\Mapper(
                'QSystem',
                new \Qore\ORM\Mapper\Driver\ArrayDriver(Qore::config('orm.QSystem'))
            );

            $mapper->setModelManager(Qore::service(\Qore\ORM\ModelManager::class));

            (new \Qore\ORM\Mapper\Engineer(
                Qore::service(\Qore\Database\Adapter\Adapter::class))
            )->inspect($mapper);

            $mm = Qore::service(\Qore\ORM\ModelManager::class);

            $users = $mm('QSystem:Users')
                ->where(function($_where) {
                    $_where(['@this.id' => 4]);
                })
                ->with('Groups')
                ->with('Profile')
                ->all();

            $user = current($users);

            $mm($user)->delete();
        }}} */

        if ($_request->getMethod() === 'POST') {
            return $this->authenticate($_request);
        }

        $loginFields = [
            'user-email' => [
                'type' => 'text',
                'label'=> 'Email',
                'placeholder' => 'Введите эл. адрес',
                'info' => 'на данный адрес будет выслано письмо для подтверждения',
            ],
            'user-password' => [
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
        $frontProtocol = $ig(Auth::class, 'auth-layout')
            ->component(
                $ig(Form::class, 'form-login')
                    ->setAction(Qore::service(UrlHelper::class)->generate($this->routeName('index')))
                    ->setFields($loginFields)
                    ->setModel($this->getAuthParams($_request))
            )->in('qore-app');

        return new HtmlResponse($this->template->render('app::login', [
            'title' => 'Авторизация',
            'frontProtocol' => $frontProtocol->compose()
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

        if (empty($authParams['user-email'])) {
            $errors['user-email'] = 'Введите ваш электронный адрес';
        }

        if (empty($authParams['user-password'])) {
            $errors['user-password'] = 'Введите пароль';
        }

        if ($errors) {
            return QoreFront\ResponseGenerator::get(
                QoreFront\Protocol\Component\QCForm::get('form-login')
                    ->setErrors($errors)
            );
        }

        Qore::service(\Qore\Auth\AuthAdapter::class)->setAuthData($authParams['user-email'], $authParams['user-password']);

        $result = $this->authService->authenticate();

        if (!$result->isValid()) {
            return QoreFront\ResponseGenerator::get(
                QoreFront\Protocol\Component\QCForm::get('form-login')
                    ->setErrors([
                        'user-email' => 'Мы вас не нашли, пожалуйста перепроверьте данные.'
                    ])
            );
        }

        return QoreFront\ResponseGenerator::get(
            QoreFront\Protocol\Layout\QLLogin::get('layout')
                ->redirect(Qore::service(UrlHelper::class)->generate($this->routeName(Index::class, 'index')))
        );
    }

    /**
     * getAuthParams
     *
     * @param ServerRequestInterface $_request
     */
    private function getAuthParams(ServerRequestInterface $_request)
    {
        return array_merge([
            'user-email' => null,
            'user-password' => null,
        ], $_request->getParsedBody());
    }
}
