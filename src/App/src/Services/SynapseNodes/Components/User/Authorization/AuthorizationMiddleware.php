<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\User\Authorization;

use Mezzio\Authentication\AuthenticationInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Qore\App\SynapseNodes\Components\User\User;
use Qore\ORM\ModelManager;
use Qore\Qore;
use Qore\Rbac\RbacContainer;
use Qore\Rbac\RbacInterface;

/**
 * Class: AuthenticateMiddleware
 *
 * @see MiddlewareInterface
 */
class AuthorizationMiddleware implements MiddlewareInterface
{
    /**
     * __construct
     *
     * @param AuthenticationInterface $_adapter
     */
    public function __construct()
    {
    }

    /**
     * process
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     */
    public function process(ServerRequestInterface $_request, RequestHandlerInterface $_handler): ResponseInterface
    {
        # - Authenticate user
        $user = $_request->getAttribute(User::class);

        $mm = Qore::service(ModelManager::class);
        $rbac = Qore::service(RbacInterface::class);

        if ($user) {
            $_request = $_request->withAttribute(RbacContainer::class, $rbac($user));
        }

        return $_handler->handle($_request);
    }

}
