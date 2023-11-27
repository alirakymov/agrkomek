<?php

declare(strict_types=1);

namespace Qore\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Authentication\AuthenticationServiceInterface;
use Mezzio\Router\RouterInterface;
use Mezzio\Router\RouteResult;
use Mezzio\Template\TemplateRendererInterface;

/**
 * Class: BaseMiddleware
 *
 * @see MiddlewareInterface
 * @abstract
 */
abstract class BaseMiddleware implements MiddlewareInterface
{
    /**
     * authService
     *
     * @var AuthenticationServiceInterface
     */
    protected $authService = null;

    /**
     * template
     *
     * @var TemplateRendererInterface
     */
    protected $template = null;

    /**
     * setAuthService
     *
     * @param AuthenticationServiceInterface $_authService
     */
    public function setAuthService(AuthenticationServiceInterface $_authService) : void
    {
        $this->authService = $_authService;
    }

    /**
     * setTemplate
     *
     * @param TemplateRendererInterface $_template
     */
    public function setTemplateService(TemplateRendererInterface $_template) : void
    {
        $this->template = $_template;
    }

    /**
     * checkRouteName
     *
     * @param ServerRequestInterface $_request
     * @param mixed $_routeName
     *
     * @return bool
     *
     */
    public function checkRouteName(ServerRequestInterface $_request, $_routeName) : bool
    {
        $routeResult = $_request->getAttribute(RouteResult::class, false);

        if (! $routeResult) {
            return false;
        }

        return $routeResult->getMatchedRouteName() === $_routeName;
    }

}
