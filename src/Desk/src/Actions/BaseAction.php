<?php

declare(strict_types=1);

namespace Qore\Desk\Actions;

use Mezzio\Router\RouteResult;
use Qore\InterfaceGateway\Component\Layout;
use Qore\InterfaceGateway\InterfaceGateway;
use Qore\NotifyManager\Command\Hub;
use Qore\NotifyManager\NotifyManager;
use Qore\Qore;
use Qore\App\Actions\ManagerIndex;
use Qore\Front as QoreFront;
use Qore\Middleware\Action\BaseActionMiddleware as QoreActionMiddleware;
use Mezzio\Helper\UrlHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

abstract class BaseAction extends QoreActionMiddleware
{
    use BaseActionNavpillsTrait;
    use BaseActionNavButtonGroupTrait;

    /**
     * accessPrivilege
     *
     * @var int
     */
    protected $accessPrivilege = 1;

    /**
     * request
     *
     * @var mixed
     */
    protected $request = null;

    /**
     * process
     *
     * @param ServerRequestInterface $_request
     * @param RequestHandlerInterface $_handler
     */
    public function process(ServerRequestInterface $_request, RequestHandlerInterface $_handler) : ResponseInterface
    {
        $this->request = $_request;
        return $this->checkPrivilege() ? $this->run() : $_handler->process($_request);
    }

    /**
     * checkPrivilege
     *
     */
    protected function checkPrivilege()
    {
        $authResult = $this->request->getAttribute('auth');

        return (int)$authResult->privilege <= $this->accessPrivilege;
    }

    /**
     * getFrontProtocol
     *
     */
    protected function getFrontProtocol()
    {
        return $this->initializeInterfaceGateway($this->request);

        $return = QoreFront\Protocol\Layout\QLMain::get('layout');
        $return->setParent('qore-app');

        if (! $this->request->isXmlHttpRequest()) {
            $return->navbar($this->getNavbar());
            $return->navpills($this->getNavpills($this->request));
            $return->navpanel($this->getNavButtonGroup($this->request));
        }

        return $return;
    }

    /**
     * Initialize interface gateway for admin interface
     *
     * @param \Psr\Http\Message\ServerRequestInterface $_request
     *
     * @return void
     */
    public function initializeInterfaceGateway(ServerRequestInterface $_request)
    {
        $ig = Qore::service(InterfaceGateway::class);
        $layout = $ig(Layout::class, 'layout');
        $layout->setParent('qore-app');
        $layout->navbar($this->getNavbar());
        $layout->navpills($this->getNavpills($_request));
        $layout->navpanel($this->getNavButtonGroup($_request));
        return $layout;
    }

    /**
     * Prepare navigation bar
     *
     * @param \Psr\Http\Message\ServerRequestInterface $_request
     * @param array $_navItems (optional)
     *
     * @return array
     */
    protected function getNavbar1(ServerRequestInterface $_request, array $_navItems = null) : array
    {
        $navigationItems = $_navItems ?? Qore::config('app.admin.navigation-items', []);
        foreach ($navigationItems as &$item) {
            if (isset($item['sublevel'])) {
                $item['sublevel'] = $this->getNavbar($_request, $item['sublevel']);
                if (! $item['sublevel']) {
                    continue;
                }
            }

            if (isset($item['route'])) {
                $route = is_string($item['route']) ? [$item['route'], 'index'] : array_values($item['route']);
                $item['route'] = Qore::service(UrlHelper::class)->generate($this->getRouteName($route[0], $route[1] ?? 'index'));
            }
        }

        return $navigationItems;
    }

    /**
     * getAjaxProtocol
     *
     */
    protected function getAjaxProtocol()
    {
        return QoreFront\Protocol\Layout\QLMain::get('layout');
    }

    /**
     * getNavbar
     *
     */
    protected function getNavbar($_navigationItems = null)
    {
        $navigationItems = $_navigationItems ?? $this->getNavigationItems();

        $return = [];
        $authResult = $this->request->getAttribute('auth');

        foreach ($navigationItems as $item) {

            if (isset($item['sublevel'])) {
                $item['sublevel'] = $this->getNavbar($item['sublevel']);
                if (! $item['sublevel']) {
                    continue;
                }
            }

            if (isset($item['privilege'])) {
                if ((int)$authResult->privilege <= (int)$item['privilege']) {
                    $return[] = $item;
                }
            } else {
                $return[] = $item;
            }
        }

        return $return;
    }

    /**
     * getNavigationItems
     *
     */
    protected function getNavigationItems()
    {
        return [
            [
                'label' => 'Системные настройки',
                'sublevel' => [
                    [
                        'label' => 'Управление доступом',
                        'icon' => 'fa fa-users',
                        'privilege' => 1,
                        'sublevel' => [
                            [
                                'label' => 'Пользователи',
                                'route' => Qore::service(UrlHelper::class)->generate($this->routeName(Users::class, 'index')),
                            ],
                        ]
                    ],
                    // [
                    //     'label' => 'Синапсы',
                    //     'privilege' => 1,
                    //     'icon' => 'fas fa-chart-network',
                    //     'route' => Qore::service(UrlHelper::class)->generate($this->routeName(Synapse\Synapses::class, 'index')),
                    // ],
                    [
                        'label' => 'Системные сервисы',
                        'privilege' => 1,
                        'icon' => 'fa fa-terminal',
                        'route' => Qore::service(UrlHelper::class)->generate($this->routeName(Services::class, 'index')),
                    ],
                    [
                        'label' => 'Панель отладки',
                        'privilege' => 1,
                        'icon' => 'fa fa-bug',
                        'route' => Qore::service(UrlHelper::class)->generate($this->routeName(Debugger::class, 'index')),
                    ],
                ],
            ],
        ];
    }
}
