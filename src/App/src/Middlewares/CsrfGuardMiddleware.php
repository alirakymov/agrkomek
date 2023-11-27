<?php

declare(strict_types=1);

namespace Qore\App\Middlewares;

use Laminas\Diactoros\Response\EmptyResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Qore\Csrf\CsrfInterface;
use Qore\Csrf\CsrfMiddleware;

class CsrfGuardMiddleware implements MiddlewareInterface
{
    /**
     * @var string - csrf generated token 
     */
    const CSRF_GENERATED_TOKEN = 'csrf-generated';

    /**
     * @var string - header name for csrf token
     */
    const CSRF_HEADER_NAME = 'csrf-token';

    /**
     * process
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     */
    public function process(ServerRequestInterface $_request, RequestHandlerInterface $_handler) : ResponseInterface
    {
        /** @var CsrfInterface */
        $guard = $_request->getAttribute(CsrfMiddleware::GUARD_ATTRIBUTE);

        if (in_array($_request->getMethod(), ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            $csrfHeader = $_request->getHeader(static::CSRF_HEADER_NAME);
            $token = array_shift($csrfHeader);
            if (! $guard->validateToken($token)) {
                return new EmptyResponse(412); // Precondition failed
            }
        }
        
        $newToken = $guard->generateToken();
        $_request = $_request->withAttribute(static::CSRF_GENERATED_TOKEN, $newToken);

        # - Generate new token and send it to client throught header
        return $_handler->handle($_request)
            ->withHeader(static::CSRF_HEADER_NAME, $newToken);
    }

}
