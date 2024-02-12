<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\System\Routes\Manager;

use Mezzio\Helper\UrlHelper;
use Mezzio\Router\RouteResult;

use Qore\App;
use Qore\App\Middlewares\AuthGuardMiddleware;
use Qore\App\Middlewares\InterfaceInitializerMiddleware;
use Qore\App\Middlewares\PermissionMiddleware;
use Qore\App\SynapseNodes;
use Qore\App\SynapseNodes\Components\Moderator\Authentication\AuthenticateMiddleware;
use Qore\App\SynapseNodes\Components\User\User;
use Qore\Desk\Actions\BaseActionNavpillsTrait;
use Qore\InterfaceGateway\Component\Auth;
use Qore\InterfaceGateway\Component\Layout;
use Qore\InterfaceGateway\InterfaceGateway;
use Qore\Qore;
use Qore\Collection\Collection;
use Qore\Router\RouteCollector;
use Qore\SynapseManager\Artificer\Form\FormArtificerInterface;
use Qore\SynapseManager\Artificer\Service;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Qore\App\SynapseNodes\Components\Moderator\Moderator;
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
        // $this->initializeInterfaceGateway($_request);

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
        $synapseMiddlewaresConfig = Qore::config('qore.route-middlewares', []);

        $result = [
            Qore::service(AuthGuardMiddleware::class),
            Qore::service(AuthenticateMiddleware::class),
            Qore::service(PermissionMiddleware::class),
            Qore::service(InterfaceInitializerMiddleware::class),
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

}
