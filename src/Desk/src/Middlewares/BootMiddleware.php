<?php

declare(strict_types=1);

namespace Qore\Desk\Middlewares;


use Qore\Qore;
use Qore\Middleware\BaseMiddleware;
use Qore\Router\RouteCollector;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class: RoutesMiddleware
 *
 * @see BaseMiddleware
 */
class BootMiddleware extends BaseMiddleware
{
    /**
     * process
     *
     * @param ServerRequestInterface $_request
     * @param DelegateInterface $_handler
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $_request, RequestHandlerInterface $_handler) : ResponseInterface
    {
        Qore::service('mm')->observers([
            // - register global observers
            \Qore\Desk\Observers\ServiceFileCombiner::class => \Qore\Core\Entities\QSystemService::class,
            \Qore\Desk\Observers\SynapseCacheCleaner::class => [
                \Qore\SynapseManager\Structure\Entity\Synapse::class,
                \Qore\SynapseManager\Structure\Entity\SynapseAttribute::class,
                \Qore\SynapseManager\Structure\Entity\SynapseRelation::class,
                \Qore\SynapseManager\Structure\Entity\SynapseService::class,
                \Qore\SynapseManager\Structure\Entity\SynapseServiceSubject::class,
                \Qore\SynapseManager\Structure\Entity\SynapseServiceAttribute::class,
                \Qore\SynapseManager\Structure\Entity\SynapseServiceForm::class,
                \Qore\SynapseManager\Structure\Entity\SynapseServiceFormField::class,
            ],
        ]);

        return $_handler->handle($_request);
    }

}
