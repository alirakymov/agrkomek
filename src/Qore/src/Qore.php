<?php

declare(strict_types=1);

namespace Qore;

use Mezzio\Helper\ServerUrlHelper;
use Mezzio\MiddlewareFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas;
use Mezzio\Helper\UrlHelper;
use Laminas\HttpHandlerRunner\RequestHandlerRunner;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Stratigility\MiddlewarePipe;
use Laminas\Stratigility\MiddlewarePipeInterface;
use Laminas\Stdlib;
use Qore\Collection\CollectionInterface;
use function Laminas\Stratigility\path;

/**
 * Class: Application
 *
 * @see MiddlewareInterface
 * @see RequestHandlerInterface
 */
class Qore implements MiddlewareInterface, RequestHandlerInterface
{
    /**
     * container
     *
     * @var \Laminas\ServiceManager\ServiceManager
     */
    private static $container = null;

    /**
     * application
     *
     * @var \Mezzio\Application;
     */
    private static $application = null;

    /**
     * stringWrapper
     *
     * @var mixed
     */
    private static $stringWrapper = null;

    /**
     * @var MiddlewareFactory
     */
    private $factory;

    /**
     * @var MiddlewarePipeInterface
     */
    private $pipeline;

    /**
     * @var RouteCollector
     */
    private $routes;

    /**
     * @var RequestHandlerRunner
     */
    private $runner;

    /**
     * init
     *
     * @param ServiceManager $_container
     * @return void
     */
    public static function init(ServiceManager $_container) : void
    {
        self::$container = $_container;
        if (! IS_CLI) {
            self::$application = self::$container->get(static::class);
        }
    }

    /**
     * service
     *
     * @param string $_service
     */
    public static function service(string $_service)
    {
        return self::$container->get($_service);
    }

    /**
     * string
     *
     */
    public static function string()
    {
        if (is_null(self::$stringWrapper)) {
            self::$stringWrapper = StdLib\StringUtils::getWrapper();
        }

        return self::$stringWrapper;
    }

    /**
     * get
     *
     * @return Mezzio\Application
     */
    public static function app() : Qore
    {
        return self::$application;
    }

    /**
     * config
     *
     * @param mixed $_param
     * @param mixed $_default
     */
    public static function config($_param, $_default = null)
    {
        $config = self::service('config');
        $_param = explode('.', $_param);


        foreach ($_param as $paramKey) {
            if (isset($config[$paramKey])) {
                $config = $config[$paramKey];
            } else {
                return $_default;
            }
        }

        return $config;
    }

    /**
     * touchDir
     *
     * @param mixed $_directory
     */
    public static function touchDir($_directory)
    {
        return touch_dir($_directory);
    }

    /**
     * pipeline
     *
     * @param array $_pipes
     */
    public static function pipeline(array $_pipes)
    {
        $pipeline = new MiddlewarePipe();

        foreach ($_pipes as $pipe) {
            $pipeline->pipe($pipe);
        }

        return $pipeline;
    }

    /**
     * container
     *
     * @return Laminas\ServiceManager\ServiceManager
     */
    public static function container() : Laminas\ServiceManager\ServiceManager
    {
        return self::$container;
    }

    /**
     * Create collection
     *
     * @param mixed $_collection
     * @return CollectionInterface
     */
    public static function collection($_collection) : CollectionInterface
    {
        return new Collection\Collection($_collection);
    }

    /**
     * url
     *
     */
    public static function url(...$_args)
    {
        return count($_args) == 0
            ? static::service(UrlHelper::class)
            : static::service(UrlHelper::class)->generate(...$_args);
    }

    /**
     * urlWithHost
     *
     * @param ... $_args
     */
    public static function urlWithHost(...$_args)
    {
        return static::service(ServerUrlHelper::class)->generate(static::url(...$_args));
    }

    /**
     * mm
     *
     */
    public static function mm(...$_args)
    {
        return count($_args) == 0
            ? static::service(ORM\ModelManager::class)
            : static::service(ORM\ModelManager::class)(...$_args);
    }

    /**
     * debug
     *
     * @param mixed $_variable
     */
    public static function debug($_variable, $_backtrace = false)
    {
        static::service('debug')->message($_variable);
        if ($_backtrace !== false) {
            $bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, (int)$_backtrace);
            array_shift($bt);
            static::service('debug')->message($bt);
            static::service('debug')->message('** --- backtrace --------------------------------- **');
        }
    }

    /**
     * dump
     *
     * @param mixed $_variable
     */
    public static function dump($_variable, $_backtrace = false)
    {
        \dump($_variable);
        if ($_backtrace !== false) {
            $bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, (int)$_backtrace);
            array_shift($bt);
            \dump($bt);
        }
    }

    /**
     * measure
     *
     * @param string $_name
     * @param Closure $_closure
     */
    public static function measure(string $_name, \Closure $_closure)
    {
        $result = $_closure();
        return $result;
    }

    /**
     * __construct
     *
     * @param Mezzio\MiddlewareFactory $factory
     * @param MiddlewarePipeInterface $pipeline
     * @param Router\RouteCollector $routes
     * @param RequestHandlerRunner $runner
     */
    public function __construct(
        MiddlewareFactory $factory,
        MiddlewarePipeInterface $pipeline,
        Router\RouteCollector $routes,
        RequestHandlerRunner $runner
    ) {
        $this->factory = $factory;
        $this->pipeline = $pipeline;
        $this->routes = $routes;
        $this->runner = $runner;
    }

    /**
     * Proxies to composed pipeline to handle.
     * {@inheritDocs}
     */
    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        return $this->pipeline->handle($request);
    }

    /**
     * Proxies to composed pipeline to process.
     * {@inheritDocs}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        return $this->pipeline->process($request, $handler);
    }

    /**
     * Run the application.
     *
     * Proxies to the RequestHandlerRunner::run() method.
     */
    public function run() : void
    {
        $this->runner->run();
    }

    /**
     * Pipe middleware to the pipeline.
     *
     * If two arguments are present, they are passed to pipe(), after first
     * passing the second argument to the factory's prepare() method.
     *
     * If only one argument is presented, it is passed to the factory prepare()
     * method.
     *
     * The resulting middleware, in both cases, is piped to the pipeline.
     *
     * @param string|array|callable|MiddlewareInterface|RequestHandlerInterface $middlewareOrPath
     *     Either the middleware to pipe, or the path to segregate the $middleware
     *     by, via a PathMiddlewareDecorator.
     * @param null|string|array|callable|MiddlewareInterface|RequestHandlerInterface $middleware
     *     If present, middleware or request handler to segregate by the path
     *     specified in $middlewareOrPath.
     */
    public function pipe($middlewareOrPath, $middleware = null) : void
    {
        $middleware = $middleware ?: $middlewareOrPath;
        $path = $middleware === $middlewareOrPath ? '/' : $middlewareOrPath;

        $middleware = $path !== '/'
            ? path($path, $this->factory->prepare($middleware))
            : $this->factory->prepare($middleware);

        $this->pipeline->pipe($middleware);
    }

    /**
     * Add a route for the route middleware to match.
     *
     * @param string|array|callable|MiddlewareInterface|RequestHandlerInterface $middleware
     *     Middleware or request handler (or service name resolving to one of
     *     those types) to associate with route.
     * @param null|array $methods HTTP method to accept; null indicates any.
     * @param null|string $name The name of the route.
     */
    public function route(string $path, $middleware, array $methods = null, string $name = null) : Router\Route
    {
        return $this->routes->route(
            $path,
            $this->factory->prepare($middleware),
            $methods,
            $name
        );
    }

    /**
     * @param string|array|callable|MiddlewareInterface|RequestHandlerInterface $middleware
     *     Middleware or request handler (or service name resolving to one of
     *     those types) to associate with route.
     * @param null|string $name The name of the route.
     */
    public function get(string $path, $middleware, string $name = null) : Router\Route
    {
        return $this->route($path, $middleware, ['GET'], $name);
    }

    /**
     * @param string|array|callable|MiddlewareInterface|RequestHandlerInterface $middleware
     *     Middleware or request handler (or service name resolving to one of
     *     those types) to associate with route.
     * @param null|string $name The name of the route.
     */
    public function post(string $path, $middleware, $name = null) : Router\Route
    {
        return $this->route($path, $middleware, ['POST'], $name);
    }

    /**
     * @param string|array|callable|MiddlewareInterface|RequestHandlerInterface $middleware
     *     Middleware or request handler (or service name resolving to one of
     *     those types) to associate with route.
     * @param null|string $name The name of the route.
     */
    public function put(string $path, $middleware, string $name = null) : Router\Route
    {
        return $this->route($path, $middleware, ['PUT'], $name);
    }

    /**
     * @param string|array|callable|MiddlewareInterface|RequestHandlerInterface $middleware
     *     Middleware or request handler (or service name resolving to one of
     *     those types) to associate with route.
     * @param null|string $name The name of the route.
     */
    public function patch(string $path, $middleware, string $name = null) : Router\Route
    {
        return $this->route($path, $middleware, ['PATCH'], $name);
    }

    /**
     * @param string|array|callable|MiddlewareInterface|RequestHandlerInterface $middleware
     *     Middleware or request handler (or service name resolving to one of
     *     those types) to associate with route.
     * @param null|string $name The name of the route.
     */
    public function delete(string $path, $middleware, string $name = null) : Router\Route
    {
        return $this->route($path, $middleware, ['DELETE'], $name);
    }

    /**
     * @param string|array|callable|MiddlewareInterface|RequestHandlerInterface $middleware
     *     Middleware or request handler (or service name resolving to one of
     *     those types) to associate with route.
     * @param null|string $name The name of the route.
     */
    public function any(string $path, $middleware, string $name = null) : Router\Route
    {
        return $this->route($path, $middleware, null, $name);
    }

    /**
     * Retrieve all directly registered routes with the application.
     *
     * @return Router\Route[]
     */
    public function getRoutes() : array
    {
        return $this->routes->getRoutes();
    }
}
