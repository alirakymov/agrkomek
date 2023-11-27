<?php

declare(strict_types=1);

namespace Qore\Csrf;

use Laminas\Diactoros\Response\EmptyResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CsrfMiddleware implements MiddlewareInterface
{
    const GUARD_ATTRIBUTE = 'csrf';

    /**
     * @var CsrfInterface
     */
    private CsrfInterface $_csrf;

    /**
     * Constructor
     *
     * @param CsrfInterface $_csrf
     */
    public function __construct(CsrfInterface $_csrf)
    {
        $this->_csrf = $_csrf;
    }

    /**
     * process
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     */
    public function process(ServerRequestInterface $_request, RequestHandlerInterface $_handler) : ResponseInterface
    {
        return $_handler->handle($_request->withAttribute($this::GUARD_ATTRIBUTE, $this->_csrf));
    }

}
