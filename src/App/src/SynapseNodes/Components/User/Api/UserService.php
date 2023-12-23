<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\User\Api;

use Mezzio\Template\TemplateRendererInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Qore\App\Services\SmsService\SmsServiceJob;
use Qore\App\SynapseNodes\Components\Consultancy\Consultancy;
use Qore\App\SynapseNodes\Components\ConsultancyMessage\ConsultancyMessage;
use Qore\DealingManager\ResultInterface;
use Qore\Qore;
use Qore\QueueManager\QueueManager;
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
            $_router->get('/code', 'code');
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

        list($method, $arguments) = $this->routingHelper->dispatch(['code']) ?? ['notFound', null];

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
    protected function code(): ?ResultInterface
    {
        $request = $this->model->getRequest();
        $queryParams = $request->getQueryParams();

        if (! isset($queryParams['phone'])) {
            return $this->response(new JsonResponse([
                'error' => 'bad request'
            ], 400));
        }

        $user = $this->mm('SM:User')->where(['@this.phone' => $queryParams['phone']])->one();

        if (is_null($user)) {
            $user = $this->mm([
                'phone' => $queryParams['phone'],
            ]);
        }

        $user->generateOtp();
        $this->mm($user)->save();

        /**@var QueueManager */
        $qm = Qore::service(QueueManager::class);
        $qm->publish(new SmsServiceJob([
            'phone' => '7' . $user['phone'],
            'code' => $user['code'],
        ]));

        return $this->response(new JsonResponse([
            'result' => 'success' 
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
