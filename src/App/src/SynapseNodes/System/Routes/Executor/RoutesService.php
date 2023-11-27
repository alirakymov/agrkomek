<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\System\Routes\Executor;

use Mezzio\Helper\UrlHelper;
use Mezzio\Router\RouteResult;
use Qore\App;
use Qore\InterfaceGateway\Component\Layout;
use Qore\InterfaceGateway\InterfaceGateway;
use Qore\Qore;
use Qore\Router\RouteCollector;
use Qore\SynapseManager\Artificer\Service;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RoutesService extends Service\ServiceArtificer
{
    /**
     * routes
     *
     * @param RouteCollector $_router
     */
    public function routes(RouteCollector $_router) : void
    {
        $_router->proxyRoutes($this, function(RouteCollector $_router) {
            $artificers = [];
            foreach ($this->getSubjectsArtificers() as $subjectArtificer) {
                # - Собираем в один массив все артифишеры субъектов и форм
                $artificers = array_merge($artificers, [$subjectArtificer], $subjectArtificer->getFormsArtificers());
            }

            $_router->registerRoutesFromMiddlewares($artificers);
        });
    }

    /**
     * process
     *
     * @param ServerRequestInterface $_request
     * @param RequestHandlerInterface $_handler
     */
    public function process(ServerRequestInterface $_request, RequestHandlerInterface $_handler) : ResponseInterface
    {
        $routeResult = $_request->getAttribute(RouteResult::class);
        $routeOptions = $routeResult->getMatchedRoute()->getOptions();
        $middleware = $routeOptions['proxing_middleware'];

        # - Create new pipeline
        $pipeline = Qore::pipeline($this->getPipelineCollectionForMiddleware($middleware));
        # - Initialize main layout InterfaceGateway component
        $this->initializeInterfaceGateway($_request);
        # - Launch pipeline
        $response = $pipeline->process($_request, $_handler);
        # - Return response
        return $response;
    }

    /**
     * getPipelineCollectionForMiddleware
     *
     * @param string $_middleware
     */
    private function getPipelineCollectionForMiddleware(string $_middleware) : array
    {
        $synapseMiddlewaresConfig = Qore::config('qore.synapse-middlewares', []);

        $result = [];
        foreach ($synapseMiddlewaresConfig as $regularExpression => $middlewares) {
            if (preg_match(sprintf('#%s#', str_replace('\\', '\\\\', $regularExpression)), $_middleware)) {
                $result = array_merge($result, $middlewares);
            }
        }

        $result[] = $_middleware;
        foreach ($result as &$middleware) {
            $middleware = $this->sm->getServicesRepository()->findByClassName($middleware) ?? Qore::service($middleware);
        }

        return $result;
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
        $ig = Qore::service(InterfaceGateway::class);
        $layout = $ig(Layout::class, 'layout');
        $layout->navbar($this->getNavbar($_request))
            ->navbuttons($this->getNavbuttons($_request));
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
        $navigationItems = $_navItems ?? Qore::config('app.cabinet.navigation-items', []);
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
     * Get navigation buttons
     *
     * @param \Psr\Http\Message\ServerRequestInterface $_request 
     *
     * @return array 
     */
    protected function getNavbuttons(ServerRequestInterface $_request): array
    {
        $navbuttons = Qore::config('app.cabinet.navigation-buttons', []);
        foreach ($navbuttons as &$item) {
            if (isset($item['route'])) {
                $route = is_string($item['route']) ? [$item['route'], 'index'] : array_values($item['route']);
                $item['route'] = Qore::service(UrlHelper::class)->generate($this->getRouteName($route[0], $route[1] ?? 'index'));
            }
        }

        return $navbuttons;
    }

}
