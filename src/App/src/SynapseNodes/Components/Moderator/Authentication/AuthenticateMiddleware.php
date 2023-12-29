<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\Moderator\Authentication;

use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Router\RouteResult;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Qore\App\Actions\Login;
use Qore\App\SynapseNodes\Components\Moderator\Moderator;
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
        # - Authenticate moderator
        $user = $this->adapter->authenticate($_request);
        if (! is_null($user)) {
            return $_handler->handle(
                $_request->withAttribute(Moderator::class, $user)
            );
        }

        # - Authenticate moderator
        if (! is_null($_request->getAttribute('admin'))) {
            return $_handler->handle($_request);
        }

        $loginRoute = Qore::service(Login::class)->routeName('index');
        $registerRoute = Qore::service(Login::class)->routeName('register');

        if (! in_array($_request->getAttribute(RouteResult::class)->getMatchedRouteName(), [$loginRoute, $registerRoute])) {
            # - Get authentication artificer
            return new RedirectResponse(Qore::url($loginRoute));
        } 

        return $_handler->handle($_request);
    }

}
