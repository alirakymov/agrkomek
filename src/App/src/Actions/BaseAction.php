<?php

declare(strict_types=1);

namespace Qore\App\Actions;

use Qore\Qore;
use Qore\Front as QoreFront;
use Qore\Middleware\Action\BaseActionMiddleware as QoreActionMiddleware;
use Mezzio\Helper\UrlHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

abstract class BaseAction extends QoreActionMiddleware
{
    /**
     * accessPrivilege
     *
     * @var int
     */
    protected $accessPrivilege = 1;

    /**
     * request
     *
     * @var mixed
     */
    protected $request = null;

    /**
     * process
     *
     * @param ServerRequestInterface $_request
     * @param RequestHandlerInterface $_handler
     */
    public function process(ServerRequestInterface $_request, RequestHandlerInterface $_handler) : ResponseInterface
    {
        $this->request = $_request;
        return $this->checkPrivilege() ? $this->run() : $_handler->process($_request);
    }

    /**
     * checkPrivilege
     *
     */
    protected function checkPrivilege()
    {
        return true;
    }

    /**
     * getRouteName
     *
     * @param string $_routeName
     */
    public function getRouteName(string $_namespace, string $_routeName = null) : string
    {
        return $this->routeName($_namespace, $_routeName);
    }

}
