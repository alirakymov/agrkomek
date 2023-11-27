<?php

declare(strict_types=1);

namespace Qore\Middleware;

use Laminas\Stratigility\Middleware\RequestHandlerMiddleware;
use Qore\Qore;
use Mezzio\Exception\MissingDependencyException;
use Mezzio\MiddlewareContainer as MezzioMiddlewareContainer;
use Mezzio\Exception\InvalidMiddlewareException;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Serializable;

/**
 * Class: MiddlewareFactory
 *
 * @see MezzioMiddlewareFactory
 */
class MiddlewareContainer extends MezzioMiddlewareContainer implements Serializable
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * __construct
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Returns true if the service is in the container, or resolves to an
     * autoloadable class name.
     *
     * @param string $service
     */
    public function has($service) : bool
    {
        if ($this->container->has($service)) {
            return true;
        }

        return class_exists($service);
    }

    /**
     * Returns middleware pulled from container, or directly instantiated if
     * not managed by the container.
     *
     * @param string $service
     * @throws Exception\MissingDependencyException if the service does not
     *     exist, or is not a valid class name.
     * @throws Exception\InvalidMiddlewareException if the service is not
     *     an instance of MiddlewareInterface.
     */
    public function get($service) : MiddlewareInterface
    {
        if (! $this->has($service)) {
            throw MissingDependencyException::forMiddlewareService($service);
        }

        $middleware = $this->container->has($service)
            ? $this->container->get($service)
            : new $service();

        if ($middleware instanceof RequestHandlerInterface
            && ! $middleware instanceof MiddlewareInterface
        ) {
            $middleware = new RequestHandlerMiddleware($middleware);
        }

        if (! $middleware instanceof MiddlewareInterface) {
            throw InvalidMiddlewareException::forMiddlewareService($service, $middleware);
        }

        return $middleware;
    }

    /**
     * serialize
     *
     */
    public function serialize() : string
    {
        return serialize(null);
    }

    /**
     * __serialize
     *
     * @return array
     */
    public function __serialize() : array
    {
        return [];
    }

    /**
     * unserialize
     *
     */
    public function unserialize($_serialized) : void
    {
        $this->container = Qore::container();
    }

    /**
     * __unserialize
     *
     * @param mixed $_data
     *
     * @return void
     */
    public function __unserialize($_data) : void
    {
        $this->container = Qore::container();
    }

}
