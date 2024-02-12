<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\User\Authentication;

use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Helper\UrlHelper;
use Mezzio\Router\RouteResult;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Qore\App\SynapseNodes\Components\User\User;
use Qore\InterfaceGateway\Component\ComponentInterface;
use Qore\InterfaceGateway\Component\Layout;
use Qore\Qore;
use Qore\SynapseManager\SynapseManager;

/**
 * Class: AuthenticateMiddleware
 *
 * @see MiddlewareInterface
 */
class InitializeUserDataMiddleware implements MiddlewareInterface
{
    /**
     * @var \Qore\InterfaceGateway\Component\ComponentInterface
     */
    protected ComponentInterface $_layout;

    /**
     * @var \Qore\SynapseManager\SynapseManager
     */
    protected SynapseManager $_sm;

    /**
     * @var \Mezzio\Helper\UrlHelper
     */
    protected UrlHelper $_url;

    /**
     * __construct
     *
     * @param AuthenticationInterface $_adapter
     */
    public function __construct(ComponentInterface $_layout, SynapseManager $_sm, UrlHelper $_url)
    {
        $this->_layout = $_layout;
        $this->_sm = $_sm;
        $this->_url = $_url;
    }

    /**
     * process
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     */
    public function process(ServerRequestInterface $_request, RequestHandlerInterface $_handler): ResponseInterface
    {
        # - Set user data to main layout InterfaceGateway component
        if (! is_null($user = $_request->getAttribute(User::class))) {
            $artificer = ($this->_sm)('User:Authentication');
            $this->_layout->setOption('user', [
                'username' => $user->username,
                'fullname' => $user->splitFullname(),
                'logoutUrl' => $this->_url->generate($artificer->getRouteName('signout')),
            ]);
        }

        return $_handler->handle($_request);
    }

}
