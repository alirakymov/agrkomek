<?php

declare(strict_types=1);

namespace Qore\App\Actions;


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
    }

    /**
     * process
     *
     * @param ServerRequestInterface $_request
     * @param DelegateInterface $_delegate
     */
    public function process(ServerRequestInterface $_request, RequestHandlerInterface $_handler) : ResponseInterface
    {
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

        if (empty($authParams['user-email'])) {
            $errors['user-email'] = 'Введите ваш электронный адрес';
        }

        if (empty($authParams['user-password'])) {
            $errors['user-password'] = 'Введите пароль';
        }

        $ig = Qore::service(InterfaceGateway::class);

        if ($errors) {
            return new JsonResponse(
                $ig(Form::class, 'form-login')->setErrors($errors)->compose()
            );
        }

        Qore::service(\Qore\Auth\AuthAdapter::class)->setAuthData($authParams['user-email'], $authParams['user-password']);

        $result = $this->authService->authenticate();

        if (! $result->isValid()) {
            return new JsonResponse(
                $ig(Form::class, 'form-login')->setErrors([
                    'user-email' => 'Мы вас не нашли, пожалуйста перепроверьте данные.'
                ])->compose()
            );
        }

        $notifyManager = Qore::service(NotifyManager::class);
        $sessionManager = Qore::service(SessionManager::class);
        $queueName = $notifyManager->subscribe($this->authService->getIdentity(), [SystemHub::class, SystemNotifyHub::class]);
        $sessionManager('NotifyService')['queueName'] = $queueName;

        return new JsonResponse($ig('auth-layout')->redirect(
            Qore::service(UrlHelper::class)->generate($this->routeName(ManagerIndex::class, 'index'))
        )->compose());
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
