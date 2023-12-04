<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\Consultancy\Api;

use Mezzio\Template\TemplateRendererInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
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
class ConsultancyService extends ServiceArtificer
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
        $_router->group('/consultancy', null, function($_router) {
            $_router->get('/token', 'token');
            $_router->get('/list', 'list');
            $_router->post('/post', 'post');
            $_router->get('/dialog', 'dialog');
            $_router->post('/message', 'message');
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

        list($method, $arguments) = $this->routingHelper->dispatch(['token', 'list', 'post', 'dialog', 'message']) ?? ['notFound', null];
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
    protected function token(): ?ResultInterface
    {
        $session = $this->mm('SM:ConsultancySession', []);
        $this->mm($session)->save();

        return $this->response(new JsonResponse([
            'token' => $session->token
        ]));
    }

    /**
     * List
     *
     * @return ?ResultInterface
     */
    protected function list(): ?ResultInterface
    {
        $request = $this->model->getRequest();
        $queryParams = $request->getQueryParams();

        if (! isset($queryParams['token'])) {
            return $this->response(new JsonResponse([
                'error' => 'bad request'
            ], 400));
        }
        
        $session = $this->mm('SM:ConsultancySession')
            ->where(['@this.token' => $queryParams['token']])
            ->one();

        if (! $session) {
            return $this->response(new JsonResponse([
                'error' => 'token not found'
            ], 404));
        }

        $data = $this->mm()->where(['@this.token' => $session->token])
            ->select(fn ($_select) => $_select->order('@this.__created desc'))
            ->all();

        $ids = $data->extract('id')->toList();

        $messages = $this->mm('SM:ConsultancyMessage')
            ->where(['@this.idConsultancy' => $ids])
            ->all()
            ->map(fn ($_message) => $_message->toArray(true));

        $data = $data->map(function ($_consultancy) use ($messages) {
            $_consultancy['messages'] = $messages->match(['idConsultancy' => $_consultancy->id])->toList();
            return $_consultancy;
        });

        $data = $data->map(fn ($_item) => $_item->toArray(true));
        return $this->response(new JsonResponse($data->toList()));
    }

    /**
     * Dialog 
     *
     * @return ?ResultInterface
     */
    protected function dialog(): ?ResultInterface
    {
        $request = $this->model->getRequest();
        $queryParams = $request->getQueryParams();

        if (! isset($queryParams['id'], $queryParams['token'])) {
            return $this->response(new JsonResponse([
                'error' => 'bad request'
            ], 400));
        }
        
        $session = $this->mm('SM:ConsultancySession')
            ->where(['@this.token' => $queryParams['token']])
            ->one();

        if (! $session) {
            return $this->response(new JsonResponse([
                'error' => 'token not found'
            ], 400));
        }

        $consultancy = $this->mm()->where(['@this.token' => $session->token, '@this.id' => $queryParams['id']])->one();

        if (! $consultancy) {
            return $this->response(new JsonResponse([
                'error' => 'dialog not found'
            ], 400));
        }

        $data = $this->mm('SM:ConsultancyMessage')
            ->where(['@this.idConsultancy' => $consultancy->id])
            ->all();

        $data = $data->map(fn ($_item) => $_item->toArray(true));
        return $this->response(new JsonResponse($data->toList()));
    }

    /**
     * Post
     *
     * @return ?ResultInterface
     */
    protected function post(): ?ResultInterface
    {
        $request = $this->model->getRequest();

        $token = $request('token');

        if (! $token) {
            return $this->response(new JsonResponse([
                'error' => 'token not found'
            ], 400));
        }

        $session = $this->mm('SM:ConsultancySession')
            ->where(['@this.token' => $token])
            ->one();

        if (! $session) {
            return $this->response(new JsonResponse([
                'error' => 'token not found'
            ], 400));
        }

        $message = $request('message');

        if (! $message) {
            return $this->response(new JsonResponse([
                'error' => 'there is no message'
            ], 400));
        }

        $consultancy = $this->mm([
            'question' => $message,
            'token' => $session->token,
        ]);

        $this->mm($consultancy)->save();

        return $this->response(
            new JsonResponse([
                'result' => 'success',
                'entity' => $consultancy->toArray(true)
            ])
        );
    }

    /**
     * Message
     *
     * @return ?ResultInterface
     */
    protected function message(): ?ResultInterface
    {
        $request = $this->model->getRequest();

        $token = $request('token');
        $idConsultancy = $request('id');

        if (! $token || ! $idConsultancy) {
            return $this->response(new JsonResponse([
                'error' => 'bad request'
            ], 400));
        }

        $session = $this->mm('SM:ConsultancySession')
            ->where(['@this.token' => $token])
            ->one();

        if (! $session) {
            return $this->response(new JsonResponse([
                'error' => 'token not found'
            ], 400));
        }

        $consultancy = $this->mm()
            ->where(['@this.token' => $session->token, '@this.id' => $idConsultancy])
            ->one();

        if (! $consultancy) {
            return $this->response(new JsonResponse([
                'error' => 'dialog not found'
            ], 400));
        }

        $message = $request('message');

        if (! $message) {
            return $this->response(new JsonResponse([
                'error' => 'there is no message'
            ], 400));
        }

        $message = $this->mm('SM:ConsultancyMessage', [
            'idConsultancy' => $consultancy->id,
            'message' => $message,
            'direction' => ConsultancyMessage::DIRECTION_IN,
        ]);

        $this->mm($message)->save();

        return $this->response(
            new JsonResponse([
                'result' => 'success',
                'entity' => $message->toArray(true)
            ])
        );
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
