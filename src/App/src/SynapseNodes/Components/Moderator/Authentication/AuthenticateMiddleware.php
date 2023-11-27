<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\Moderator\Authentication;

use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Authentication\AuthenticationInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Qore\App\SynapseNodes\Components\User\User;
use Qore\Qore;
use Qore\SynapseManager\SynapseManager;

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
     * @var \Qore\SynapseManager\SynapseManager
     */
    protected SynapseManager $_sm;

    /**
     * __construct
     *
     * @param AuthenticationInterface $_adapter
     */
    public function __construct(AuthenticationInterface $_adapter, SynapseManager $_sm)
    {
        $this->adapter = $_adapter;
        $this->_sm = $_sm;
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
        $user = $this->adapter->authenticate($_request);
        if (! is_null($user)) {
            return $_handler->handle(
                $_request->withAttribute(User::class, $user)
            );
        }
        # - Get authentication artificer
        $artificer = ($this->_sm)('User:Authentication');
        return new RedirectResponse(
            Qore::url($artificer->getRouteName('signin'))
        );
    }

}
