<?php

declare(strict_types=1);

namespace Qore\App\Middlewares;

use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Router\RouteResult;
use Qore\App\SynapseNodes\Components\Moderator\Moderator;
use Qore\Desk\Actions\BaseActionNavpillsTrait;
use Qore\InterfaceGateway\Component\Layout;
use Qore\InterfaceGateway\InterfaceGateway;
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
use Qore\SynapseManager\SynapseManager;
use Qore\Tracking\TrackingInterface;

/**
 * Class: AuthGuardMiddleware
 *
 * @see BaseMiddleware
 */
class InterfaceInitializerMiddleware extends BaseMiddleware
{
    use BaseActionNavpillsTrait;

    /**
     * process
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     */
    public function process(ServerRequestInterface $_request, RequestHandlerInterface $_handler) : ResponseInterface
    {
        $this->initializeInterfaceGateway($_request);

        return $_handler->handle($_request);
    }

    /**
     * Initialize interface gateway for admin interface
     *
     * @param \Psr\Http\Message\ServerRequestInterface $_request
     *
     * @return void
     */
    public function initializeInterfaceGateway(ServerRequestInterface $_request) : void
    {
        // $moderator = $_request->getAttribute(Moderator::class);

        $ig = Qore::service(InterfaceGateway::class);
        $layout = $ig(Layout::class, 'layout');
        $layout->navbar($this->getNavbar($_request));
        $layout->navpills($this->getNavpills($_request));
    }

    /**
     * Prepare navigation bar
     *
     * @param \Psr\Http\Message\ServerRequestInterface $_request
     * @param array $_navItems (optional)
     *
     * @return array
     */
    protected function getNavbar(ServerRequestInterface $_request, array $_navItems = null) : array
    {
        $moderator = $_request->getAttribute(Moderator::class);
        $isAdmin = ! is_null($_request->getAttribute('admin', null));

        $navigationItems = $_navItems ?? Qore::config('app.admin.navigation-items', []);

        /**@var SynapseManager */
        $sm = Qore::service(SynapseManager::class);

        foreach ($navigationItems as $key => &$item) {
            if (isset($item['sublevel'])) {
                $item['sublevel'] = $this->getNavbar($_request, $item['sublevel']);
                if (! $item['sublevel']) {
                    unset($navigationItems[$key]);
                    continue;
                }
            }

            if (isset($item['route'])) {
                $route = is_string($item['route']) ? [$item['route'], 'index'] : array_values($item['route']);
                
                if ($moderator && ! $moderator->checkPermission($route[0]) && ! $isAdmin) {
                    unset($navigationItems[$key]);
                    continue;
                }

                $artificer = $sm->getServicesRepository()->findByClassName($route[0]);
                $item['route'] = Qore::service(UrlHelper::class)->generate($artificer->getRouteName($route[1] ?? 'index'));
            }
        }

        return $navigationItems;
    }

}
