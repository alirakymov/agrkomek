<?php

declare(strict_types=1);

namespace Qore\Middleware;

use Mezzio\Middleware\LazyLoadingMiddleware as MezzioLazyLoadingMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class: LazyLoadingMiddleware
 *
 * @see MezzioLazyLoadingMiddleware
 */
class LazyLoadingMiddleware extends MezzioLazyLoadingMiddleware
{
    /**
     * @var MiddlewareContainer
     */
    private $container;

    /**
     * @var string
     */
    private $middlewareName;

    public function __construct(
        MiddlewareContainer $container,
        string $middlewareName
    ) {
        $this->container = $container;
        $this->middlewareName = $middlewareName;
    }

    /**
     * @throws InvalidMiddlewareException for invalid middleware types pulled
     *     from the container.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $middleware = $this->container->get($this->middlewareName);
        return $middleware->process($request, $handler);
    }
}
