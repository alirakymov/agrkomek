<?php

declare(strict_types=1);

namespace Qore\Desk\Actions;


use Qore\Middleware\Action\BaseActionMiddleware;
use Qore\Qore;
use Qore\Front as QoreFront;
use Qore\Router\RouteCollector;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Helper\UrlHelper;

class Inspect extends BaseActionMiddleware
{
    /**
     * routes
     *
     * @param RouteCollector $_router
     */
    public function routes(RouteCollector $_router) : void
    {
        $_router->any('/inspect', 'index');
    }

    /**
     * process
     *
     * @param ServerRequestInterface $_request
     * @param DelegateInterface $_delegate
     */
    public function process(ServerRequestInterface $_request, RequestHandlerInterface $_handler) : ResponseInterface
    {
        $results = [];

        $engineer = new \Qore\ORM\Mapper\Engineer(
            Qore::service(\Qore\Database\Adapter\Adapter::class)
        );

        $systemMapper = new \Qore\ORM\Mapper\Mapper(
            'QSystem',
            new \Qore\ORM\Mapper\Driver\ArrayDriver(Qore::config('orm.QSystem'))
        );
        $systemMapper->setModelManager(Qore::service(\Qore\ORM\ModelManager::class));
        $result = $engineer->inspect($systemMapper);
        $results[] = $result->getModifiedModels();
        $result->applyChanges();

        $synapseMapper = new \Qore\ORM\Mapper\Mapper(
            'QSynapse',
            new \Qore\ORM\Mapper\Driver\ArrayDriver(Qore::config('orm.QSynapse'))
        );
        $synapseMapper->setModelManager(Qore::service(\Qore\ORM\ModelManager::class));
        $result = $engineer->inspect($synapseMapper);
        $results[] = $result->getModifiedModels();
        $result->applyChanges();

        $sm = Qore::service(\Qore\SynapseManager\SynapseManager::class);
        $result = $engineer->inspect($sm->getMapper());
        $results[] = $result->getModifiedModels();
        $result->applyChanges();

        return QoreFront\ResponseGenerator::get();

    }
}
