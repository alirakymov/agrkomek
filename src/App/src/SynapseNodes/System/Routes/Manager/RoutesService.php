<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\System\Routes\Manager;

use Mezzio\Helper\UrlHelper;
use Mezzio\Router\RouteResult;

use Qore\App;
use Qore\App\SynapseNodes;
use Qore\App\SynapseNodes\Components\User\User;
use Qore\Desk\Actions\BaseActionNavpillsTrait;
use Qore\InterfaceGateway\Component\Auth;
use Qore\InterfaceGateway\Component\Layout;
use Qore\InterfaceGateway\InterfaceGateway;
use Qore\NotifyManager\NotifyManager;
use Qore\Qore;
use Qore\Collection\Collection;
use Qore\Router\RouteCollector;
use Qore\SynapseManager\Artificer\Form\FormArtificerInterface;
use Qore\SynapseManager\Artificer\Service;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Qore\SynapseManager\Artificer\Service\ServiceArtificer;
use Qore\SynapseManager\Artificer\Service\ServiceArtificerInterface;


/**
 * Class: Service
 *
 * @see SynapseNodes\BaseManagerServiceArtificer
 */
class RoutesService extends ServiceArtificer
{
    use BaseActionNavpillsTrait;

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

        # - Initialize interface gateway for admin layout
        $this->initializeInterfaceGateway($_request);

        # - return response
        return $pipeline->process($_request, $_handler);
    }

    /**
     * getPipelineCollectionForMiddleware
     *
     * @param string $_middleware
     */
    private function getPipelineCollectionForMiddleware(string $_middleware) : array
    {
        $synapseMiddlewaresConfig = Qore::config('qore.synapse-middlewares', []);

        $result = [
            Qore::service(App\Middlewares\AuthGuardMiddleware::class),
            Qore::service(App\Middlewares\NotifySubscriberMiddleware::class)
        ];

        foreach ($synapseMiddlewaresConfig as $regularExpression => $middlewares) {
            if (preg_match(sprintf('#%s#', str_replace('\\', '\\\\', $regularExpression)), $_middleware)) {
                $result = array_merge($result, $middlewares);
            }
        }

        $result[] = $_middleware;
        foreach ($result as &$middleware) {
            if (is_string($middleware)) {
                # - if it's Synapse:Service artificer
                in_array(ServiceArtificerInterface::class, class_implements($middleware) ?: [])
                    && ($middleware = $this->sm->getServicesRepository()->findByClassName($middleware))
                # - or it's Synapse:Service#Form artificer
                || in_array(FormArtificerInterface::class, class_implements($middleware) ?: [])
                    && ($middleware = $this->sm->getFormsRepository()->findByClassName($middleware))
                # - or it's middleware service
                || ($middleware = Qore::service($middleware));
            }
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

}
