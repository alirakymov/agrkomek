<?php

declare(strict_types=1);

namespace Qore\Desk\Actions;

use Qore\Qore;
use Qore\Front as QoreFront;
use Qore\Router\RouteCollector;
use Qore\Db\TableGateway\TableGateway;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Mezzio\Helper\UrlHelper;
use PAMI\Client\Impl\ClientImpl as PamiClient;
use PAMI\Message\Action\OriginateAction;

class Index extends BaseAction
{
    /**
     * accessPrivilege
     *
     * @var int
     */
    protected $accessPrivilege = 100;

    /**
     * polisSearch
     *
     * @var mixed
     */
    private $polisSearch = null;

    /**
     * Return routes array
     *
     * @return array
     */
    public function routes(RouteCollector $_router) : void
    {
        $_router->any('', 'index');
    }

    /**
     * run
     *
     */
    protected function run()
    {
        return $this->runIndex();
    }

    /**
     * runIndex
     *
     */
    protected function runIndex()
    {

        $frontProtocol = $this->getFrontProtocol();

        return new HtmlResponse($this->template->render('app::main', [
            'title' => 'Qore.CRM',
            'frontProtocol' => $frontProtocol->compose()
        ]));
    }

}
