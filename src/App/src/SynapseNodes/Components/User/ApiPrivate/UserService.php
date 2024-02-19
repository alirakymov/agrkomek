<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\User\ApiPrivate;

use Mezzio\Template\TemplateRendererInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Mezzio\Authentication\UserInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Qore\App\SynapseNodes\Components\Consultancy\Consultancy;
use Qore\App\SynapseNodes\Components\ConsultancyMessage\ConsultancyMessage;
use Qore\DealingManager\ResultInterface;
use Qore\Qore;
use Qore\Router\RouteCollector;
use Qore\SynapseManager\Artificer\Service\ServiceArtificer;
use Qore\SynapseManager\Plugin\RoutingHelper\RoutingHelper;

/**
 * Class: ConsultancyService 
 *
 * @see Qore\SynapseManager\Artificer\Service\ServiceArtificer
 */
class UserService extends ServiceArtificer
{
    /**
     * serviceForm
     *
     * @var string
     */
    private $serviceForm = '';

    /**
     * @var \Qore\SynapseManager\Plugin\RoutingHelper\RoutingHelper
     */
    private RoutingHelper $routingHelper;

    /**
     * routes
     *
     * @param RouteCollector $_router
     */
    public function routes(RouteCollector $_router) : void
    {
        $this->routingHelper = $this->plugin(RoutingHelper::class);
        $_router->group('/user', null, function($_router) {
            $_router->post('/profile', 'profile');
            $_router->get('/info', 'info');
            $_router->post('/delete', 'delete');
        });
        # - Register related subjects routes
        $this->registerSubjectsRoutes($_router);
        # - Register this service forms routes
        $this->registerFormsRoutes($_router);
    }

    /**
     * Execute current service
     *
     * @return ?ResultInterface
     */
    public function compile() : ?ResultInterface
    {
        $routeResult = $this->model->getRouteResult();

        $this->routingHelper = $this->plugin(RoutingHelper::class);

        list($method, $arguments) = $this->routingHelper->dispatch(['profile', 'info', 'delete']) ?? ['notFound', null];

        return ! is_null($method) ? call_user_func_array([$this, $method], $arguments ?? []) : null;
    }

    /**
     * Index action for index route
     *
     * @return ?ResultInterface
     */
    protected function index() : ?ResultInterface
    {
        return $this->response(new HtmlResponse('Hi from Qore\App\SynapseNodes\Components\Article\Api - ArticleService'));
    }

    /**
     * Token 
     *
     * @return ?ResultInterface
     */
    protected function profile(): ?ResultInterface
    {
        $request = $this->model->getRequest();
        $queryParams = $request->getQueryParams();
        /**@var UserInterface */
        $user = $request->getAttribute(UserInterface::class);
        $user = $this->mm('SM:User')->where(['@this.phone' => $user->getIdentity()])->one();

        $parsedBody = $request->getParsedBody();

        if (isset($parsedBody['firstname']) || isset($parsedBody['secondname'])) {
            isset($parsedBody['firstname']) && $user['firstname'] = $parsedBody['firstname'];
            isset($parsedBody['secondname']) && $user['secondname'] = $parsedBody['secondname'];

            $this->mm($user)->save();
        }

        return $this->response(new JsonResponse([
            'result' => 'success',
            'entity' => $user,
        ]));
    }

    /**
     * Delete
     *
     * @return ?ResultInterface
     */
    protected function delete(): ?ResultInterface
    {
        $request = $this->model->getRequest();
        $queryParams = $request->getQueryParams();
        /**@var UserInterface */
        $user = $request->getAttribute(UserInterface::class);
        $user = $this->mm('SM:User')->where(['@this.phone' => $user->getIdentity()])->one();

        if ($user) {
            $this->mm($user)->delete();
        }

        return $this->response(new JsonResponse([
            'result' => 'success',
        ]));
    }

    /**
     * Token 
     *
     * @return ?ResultInterface
     */
    protected function info(): ?ResultInterface
    {
        $request = $this->model->getRequest();
        $queryParams = $request->getQueryParams();
        
        /**@var UserInterface */
        $user = $request->getAttribute(UserInterface::class);
        $user = $this->mm('SM:User')->where(['@this.phone' => $user->getIdentity()])->one();

        return $this->response(new JsonResponse([
            'user' => $user,
        ]));
    }

    /**
     * Not Found
     *
     * @return ?ResultInterface
     */
    protected function notFound() : ?ResultInterface
    {
        return $this->response(new JsonResponse([
            'error' => 'resource not found'
        ], 404));
    }

}
