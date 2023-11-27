<?php

declare(strict_types=1);

namespace Qore\Desk\Actions;

use Qore\CacheManager;
use Qore\QoreConsole as ConsoleApplication;
use Qore\Middleware\Action\BaseActionMiddleware;
use Qore\Qore;
use Qore\Front as QoreFront;
use Qore\Router\RouteCollector;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Helper\UrlHelper;

class CacheCleaner extends BaseActionMiddleware
{
    /**
     * routes
     *
     * @param RouteCollector $_router
     */
    public function routes(RouteCollector $_router) : void
    {
        $_router->any('/cacheclear', 'index');
    }

    /**
     * process
     *
     * @param ServerRequestInterface $_request
     * @param DelegateInterface $_delegate
     */
    public function process(ServerRequestInterface $_request, RequestHandlerInterface $_handler) : ResponseInterface
    {
        $input = new ArrayInput([
            'command' => 'cache:clear',
        ]);
        # - Чистим cache
        Qore::service(ConsoleApplication::class)->run($input);
        # - Возвращаем правильный результат
        return QoreFront\ResponseGenerator::get();
    }
}
