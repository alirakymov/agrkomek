<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\System\API\Authentication;

use Mezzio\Authentication\AuthenticationInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Qore\App\SynapseNodes\System\API\Merchant as Subject;

/**
 * Class: AuthenticateMiddleware
 *
 * @see MiddlewareInterface
 */
class AuthenticateMiddleware implements MiddlewareInterface
{
    /**
     * adapter
     *
     * @var mixed
     */
    private $adapter = null;

    /**
     * __construct
     *
     * @param AuthenticationInterface $_adapter
     */
    public function __construct(AuthenticationInterface $_adapter)
    {
        $this->adapter = $_adapter;
    }

    /**
     * process
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     */
    public function process(ServerRequestInterface $_request, RequestHandlerInterface $_handler) : ResponseInterface
    {
        $subject = $this->adapter->authenticate($_request);

        if (! is_null($subject)) {
            return $_handler->handle(
                $_request->withAttribute(Subject::class, $subject)
            );
        }

        return $this->adapter->unauthorizedResponse($_request);
    }

}
