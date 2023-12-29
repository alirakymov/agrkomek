<?php

declare(strict_types=1);

namespace Qore\App\Actions;

use GuzzleHttp\Psr7\Response;
use Mezzio\Template\TemplateRendererInterface;
use Qore\InterfaceGateway\InterfaceGateway;
use Qore\Qore;
use Qore\Router\RouteCollector;
use Laminas\Diactoros\Response\HtmlResponse;
use Qore\SynapseManager\SynapseManager;

/**
 * Class: ManagerIndex
 *
 * @see BaseAction
 */
class ManagerIndex extends BaseAction
{
    /**
     * accessPrivilege
     *
     * @var int
     */
    protected $accessPrivilege = 1;

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
     * Run
     *
     * @return Response
     */
    protected function run()
    {
        $routeResult = $this->request->getAttribute(\Mezzio\Router\RouteResult::class);
        return $this->runIndex();
    }

    /**
     * runIndex
     *
     */
    protected function runIndex()
    {
        // $sm = Qore::service(SynapseManager::class);

        // $sm('Routes:Manager')->initializeInterfaceGateway($this->request);

        $ig = Qore::service(InterfaceGateway::class);
        $content = Qore::service(TemplateRendererInterface::class)->render('app::main', [
            'title' => 'Панель управления',
            'frontProtocol' => $ig('layout')->compose(),
        ]);

        return new HtmlResponse($content);
    }

}
