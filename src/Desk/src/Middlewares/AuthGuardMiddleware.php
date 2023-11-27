<?php

namespace Qore\Desk\Middlewares;

use Qore\Qore;
use Qore\Middleware\BaseMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Mezzio\Helper\UrlHelper;

class AuthGuardMiddleware extends BaseMiddleware
{
    /**
     * process
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     */
    public function process(ServerRequestInterface $_request, RequestHandlerInterface $_handler) : ResponseInterface
    {
        $loginRoute = Qore::service(\Qore\Desk\Actions\Login::class)->routeName('index');
        $indexRoute = Qore::service(\Qore\Desk\Actions\Index::class)->routeName('index');

        if (! $this->authService->hasIdentity() && ! $this->checkRouteName($_request, $loginRoute)) {
            return new RedirectResponse(Qore::service(UrlHelper::class)->generate($loginRoute));
        } elseif ($this->authService->hasIdentity() && $this->checkRouteName($_request, $loginRoute)) {
            return new RedirectResponse(Qore::service(UrlHelper::class)->generate($indexRoute));
        }

        return $_handler->handle($_request->withAttribute('auth', $this->authService->getIdentity()));
    }
}
