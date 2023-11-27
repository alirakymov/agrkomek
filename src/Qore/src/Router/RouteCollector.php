<?php

declare(strict_types=1);

namespace Qore\Router;

use Mezzio\MiddlewareFactory;
use Mezzio\Router\RouterInterface;
use Qore\Qore;
use Qore\Middleware;
use Mezzio\Router\Route;
use Mezzio\Router\RouteResult;
use Mezzio\Router\Exception as ZRouterException;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;

class RouteCollector
{
    /**
     * currentRouteGroupPrefix
     *
     * @var string
     */
    protected $currentRoutePathPrefix = '';

    /**
     * currentProxyRouteNamespace
     *
     * @var string
     */
    protected $currentRouteNamespace = null;

    /**
     * proxyMiddleware
     *
     * @var string
     */
    protected $proxyMiddleware = null;

    /**
     * proxingMiddleware
     *
     * @var string
     */
    protected $proxingMiddleware = null;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * List of all routes registered directly with the application.
     *
     * @var Route[]
     */
    private $routes = [];

    /**
     * __construct
     *
     * @param Mezzio\Router\RouterInterface $router
     */
    public function __construct(RouterInterface $router) {
        $this->router = $router;
    }

    /**
     * isRoutesInitialized
     *
     */
    public function isRoutesInitialized()
    {
        return $this->router->isUnserialized();
    }

    /**
     * group
     *
     * @param string $_pathPrefix
     * @param string $_namespace
     * @param \Closure $_callback
     */
    public function group(string $_pathPrefix = null, string $_namespace = null, \Closure $_callback) : void
    {
        $currentRoutePathPrefix = $this->currentRoutePathPrefix;
        $this->currentRoutePathPrefix = $currentRoutePathPrefix . ($_pathPrefix ?? '');

        $currentRouteNamespace = $this->currentRouteNamespace;
        $this->currentRouteNamespace = $_namespace ? $this->concatNamespace($_namespace) : $currentRouteNamespace;

        $_callback($this);

        $this->currentRoutePathPrefix = $currentRoutePathPrefix;
        $this->currentRouteNamespace = $currentRouteNamespace;
    }

    /**
     * getRouteNamespace
     *
     */
    public function getRouteNamespace()
    {
        return $this->currentRouteNamespace;
    }

    /**
     * proxyRoutes
     *
     * @param mixed $_middleware
     * @param \Closure $_callback
     */
    public function proxyRoutes($_middleware, \Closure $_callback) : void
    {
        if (is_object($_middleware)) {
            $_middleware = get_class($_middleware);
        }

        $currentProxyMiddleware = $this->proxyMiddleware;
        $this->proxyMiddleware = $_middleware;

        $_callback($this);

        $this->proxyMiddleware = $currentProxyMiddleware;
    }

    /**
     * registerRoutesFromActions
     *
     * @param array $_actions
     */
    public function registerRoutesFromMiddlewares(array $_middlewares) : void
    {
        foreach ($_middlewares as $middleware) {

            if (is_string($middleware) && ! class_exists($middleware, true)) {
                throw new Exception\FailedAction(sprintf('Failed action %s for QoreFramework routing', $middleware));
            } elseif (is_string($middleware)) {
                $middlewareInstance = Qore::service($middleware);
            }

            if (is_object($middleware) && ! is_callable([$middleware, 'routes'])) {
                throw new Exception\FailedAction(sprintf('Undefined method %s::routes() for QoreFramework routing ', $middleware));
            } elseif (is_object($middleware)) {
                $middlewareInstance = $middleware;
                $middleware = get_class($middleware);
            }

            if (! $middlewareInstance instanceof Middleware\Action\ActionMiddlewareInterface) {
                throw new Exception\FailedAction('Failed action for QoreFramework routing');
            }

            // Remember current proxing middleware
            $currentProxingMiddleware = $this->proxingMiddleware;
            // Set new proxing middleware
            $this->proxingMiddleware = $middleware;

            $this->group(null, $middleware, function($_router) use ($middlewareInstance) {
                $middlewareInstance->routes($_router);
            });

            // Set current proxing middleware
            $this->proxingMiddleware = $currentProxingMiddleware;
        }
    }

    /**
     * Add a route for the route middleware to match.
     *
     * Accepts a combination of a path and middleware, and optionally the HTTP methods allowed.
     *
     * @param null|array $methods HTTP method to accept; null indicates any.
     * @param null|string $name The name of the route.
     * @throws ZRouterException\DuplicateRouteException if specification represents an existing route.
     */
    public function route(
        string $path,
        array $methods = null,
        string $name = null,
        $middleware = null
    ) : Route {

        $isProxyRoute = false;
        if ($middleware === null) {

            $isProxyRoute = true;
            if ($this->proxyMiddleware === null) {
                throw new ZRouterException\UndefinedRouteMiddleware('Middleware for route ' . $path . ' is not defined and $proxyMiddleware is NULL');
            }

            // Use proxy middleware
            $middleware = $this->proxyMiddleware;
        }

        $path = $this->currentRoutePathPrefix . $path;
        $name = $this->concatNamespace($name);

        $this->checkForDuplicateRoute($path, $methods);

        $methods = null === $methods ? Route::HTTP_METHOD_ANY : $methods;
        $route   = new Route(
            $path,
            Qore::service(MiddlewareFactory::class)->prepare($middleware),
            $methods,
            $name
        );

        if ($isProxyRoute) {
            $route->setOptions(array_merge($route->getOptions(), [
                'proxing_middleware' => $this->proxingMiddleware,
            ]));
        }

        $this->routes[] = $route;
        $this->router->addRoute($route);

        return $route;
    }

    /**
     * @param null|string $name The name of the route.
     */
    public function get(string $path, string $name = null, $middleware = null) : Route
    {
        return $this->route($path, ['GET'], $name, $middleware);
    }

    /**
     * @param null|string $name The name of the route.
     */
    public function post(string $path, string $name = null, $middleware = null) : Route
    {
        return $this->route($path, ['POST'], $name, $middleware);
    }

    /**
     * @param null|string $name The name of the route.
     */
    public function put(string $path, string $name = null, $middleware = null) : Route
    {
        return $this->route($path, ['PUT'], $name, $middleware);
    }

    /**
     * @param null|string $name The name of the route.
     */
    public function patch(string $path, string $name = null, $middleware = null) : Route
    {
        return $this->route($path, ['PATCH'], $name, $middleware);
    }

    /**
     * @param null|string $name The name of the route.
     */
    public function delete(string $path, string $name = null, $middleware = null) : Route
    {
        return $this->route($path, ['DELETE'], $name, $middleware);
    }

    /**
     * @param null|string $name The name of the route.
     */
    public function any(string $path, string $name = null, $middleware = null) : Route
    {
        return $this->route($path, null, $name, $middleware);
    }

    /**
     * Retrieve all directly registered routes with the application.
     *
     * @return Route[]
     */
    public function getRoutes() : array
    {
        return $this->routes;
    }

    /**
     * Determine if the route is duplicated in the current list.
     *
     * Checks if a route with the same name or path exists already in the list;
     * if so, and it responds to any of the $methods indicated, raises
     * a DuplicateRouteException indicating a duplicate route.
     *
     * @throws ZRouterException\DuplicateRouteException on duplicate route detection.
     */
    private function checkForDuplicateRoute(string $path, array $methods = null) : void
    {
        if (null === $methods) {
            $methods = Route::HTTP_METHOD_ANY;
        }

        $matches = array_filter($this->routes, function (Route $route) use ($path, $methods) {
            if ($path !== $route->getPath()) {
                return false;
            }

            if ($methods === Route::HTTP_METHOD_ANY) {
                return true;
            }

            return array_reduce($methods, function ($carry, $method) use ($route) {
                return ($carry || $route->allowsMethod($method));
            }, false);
        });

        if (! empty($matches)) {
            $match = reset($matches);
            $allowedMethods = $match->getAllowedMethods() ?: ['(any)'];
            $name = $match->getName();
            throw new ZRouterException\DuplicateRouteException(sprintf(
                'Duplicate route detected; path "%s" answering to methods [%s]%s',
                $match->getPath(),
                implode(',', $allowedMethods),
                $name ? sprintf(', with name "%s"', $name) : ''
            ));
        }
    }

    /**
     * concatNamespace
     *
     * @param string $_namespace
     * @param string $_name
     * @return string
     */
    private function concatNamespace($_namespace, string $_name = null) : string
    {
        if ($_name === null) {

            if ($_namespace === null) {
                return null;
            }

            $_name = $_namespace;
            $_namespace = $this->currentRouteNamespace;
        }

        return ($_namespace ? $_namespace . '.' : '') . ($_name ?? '');
    }
}
