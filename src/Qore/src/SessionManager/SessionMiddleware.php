<?php

namespace Qore\SessionManager;

use Dflydev\FigCookies\SetCookie;
use Dflydev\FigCookies\SetCookies;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class: SessionMiddleware
 *
 */
class SessionMiddleware implements MiddlewareInterface
{
    public const SESSION_ATTRIBUTE = 'qore-session';

    /**
     * @var SessionPersistenceInterface
     */
    private $manager;

    /**
     * __construct
     *
     * @param SessionManager $_sessionManager
     */
    public function __construct(SessionManager $_sessionManager)
    {
        $this->manager = $_sessionManager;
    }

    /**
     * process
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        return $handler->handle($request->withAttribute(self::SESSION_ATTRIBUTE, $this->manager));
    }

}

