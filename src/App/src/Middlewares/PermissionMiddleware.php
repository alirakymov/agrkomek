<?php

declare(strict_types=1);

namespace Qore\App\Middlewares;

use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Router\RouteResult;
use Qore\App\Actions\ManagerIndex;
use Qore\App\SynapseNodes\Components\Moderator\Moderator;
use Qore\ORM\ModelManager;
use Qore\Qore;
use Qore\Middleware\BaseMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Mezzio\Helper\UrlHelper;
use Qore\App\Services\UserStack\UserStackInterface;
use Qore\Tracking\TrackingInterface;

/**
 * Class: AuthGuardMiddleware
 *
 * @see BaseMiddleware
 */
class PermissionMiddleware extends BaseMiddleware
{
    /**
     * process
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     */
    public function process(ServerRequestInterface $_request, RequestHandlerInterface $_handler) : ResponseInterface
    {
        $admin = $_request->getAttribute('admin');
        if ($admin) {
            return $_handler->handle($_request);
        }

        if ($moderator = $_request->getAttribute(Moderator::class)) {

            $mm = Qore::service(ModelManager::class);
            $mm($moderator)->with('role', function($_gw) {
                $_gw->with('permissions');
            })->one();

            /** @var RouteResult */
            $routeResult = $_request->getAttribute(RouteResult::class);
            $component = $routeResult->getMatchedRoute()->getOptions()['proxing_middleware'] ?? '';
            
            if ($moderator->checkPermission($component) || $component == ManagerIndex::class) {
                return $_handler->handle($_request);
            } else {
                return new HtmlResponse('Access Denied', 403);
            }
        }

        return $_handler->handle($_request);

    }

}
