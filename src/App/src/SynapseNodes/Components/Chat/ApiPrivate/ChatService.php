<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\Chat\ApiPrivate;

use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Mezzio\Authentication\UserInterface;
use Qore\App\SynapseNodes\Components\Chat\Chat;
use Qore\App\SynapseNodes\Components\Machinery\Machinery;
use Qore\DealingManager\ResultInterface;
use Qore\Router\RouteCollector;
use Qore\SynapseManager\Artificer\Service\ServiceArtificer;
use Qore\SynapseManager\Plugin\RoutingHelper\RoutingHelper;

/**
 * Class: ArticleService
 *
 * @see Qore\SynapseManager\Artificer\Service\ServiceArtificer
 */
class ChatService extends ServiceArtificer
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
        $_router->group('/chat', null, function($_router) {
            $_router->post('/save', 'save');
            $_router->get('/user-list', 'user-list');
            $_router->get('/list', 'list');
            $_router->get('/delete', 'delete');
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

        list($method, $arguments) = $this->routingHelper->dispatch(['save', 'user-list' => 'userList', 'list']) ?? ['notFound', null];
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
     * List
     *
     * @return ?ResultInterface
     */
    protected function save(): ?ResultInterface
    {
        $request = $this->model->getRequest();

        $data = $this->validate($request->getParsedBody());

        if (is_string($data)) {
            return $this->response(new JsonResponse([
                'result' => 'bad request',
                'attribute' => $data,
            ], 400));
        }

        $entity = $this->getChatEntity($data);

        if (is_null($data)) {
            return $this->response(new JsonResponse([
                'result' => 'bad request'
            ], 400));
        }

        $this->mm($entity)->save();

        return $this->response(new JsonResponse([
            'result' => 'success',
            'entity' => $entity,
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
        /**@var UserInterface */
        $user = $request->getAttribute(UserInterface::class);

        $user = $this->mm('SM:User')->where(['@this.phone' => $user->getIdentity()])->one();

        $gw = $this->mm()
            ->select(fn ($_select) => $_select->order('@this.__updated desc'));

        $data = $gw->all();

        $data = $data->map(fn ($_item) => $_item->toArray(true));
        return $this->response(new JsonResponse($data->toList()));
    }

    /**
     * List
     *
     * @return ?ResultInterface
     */
    protected function userList(): ?ResultInterface
    {
        $request = $this->model->getRequest();
        $queryParams = $request->getQueryParams();
        /**@var UserInterface */
        $user = $request->getAttribute(UserInterface::class);

        $user = $this->mm('SM:User')->where(['@this.phone' => $user->getIdentity()])->one();

        $filters = [
            '@this.idUser' => $user->id
        ];

        $gw = $this->mm()
            ->select(fn ($_select) => $_select->order('@this.__updated desc'));

        if ($filters) {
            $gw->where($filters);
        }

        $data = $gw->all();

        $data = $data->map(fn ($_item) => $_item->toArray(true));
        return $this->response(new JsonResponse($data->toList()));
    }

    /**
     * Validate data
     *
     * @param array $_data
     *
     * @return string
     */
    private function validate(array $_data): array|string
    {
        $rules = [
            'id' => function($_value) {
                return is_null($_value) || preg_match('/\d+/', (string)$_value);
            },
            'title' => function($_value) {
                return ! empty($_value);
            },
        ];

        $result = true;

        $data = [];

        foreach ($rules as $attribute => $rule) {
            $isValid = $rule($_data[$attribute] ?? null);

            if (! $isValid) {
                return $attribute;
            }

            if (isset($_data[$attribute])) {
                $data[$attribute] = $_data[$attribute];
            }
        }

        return $data;
    }

    /**
     * Get entity 
     *
     * @param array $_data 
     *
     * @return \Qore\App\SynapseNodes\Components\Machinery\Machinery
     */
    private function getChatEntity(array $_data): Chat|null
    {
        $request = $this->model->getRequest();
        /**@var UserInterface */
        $user = $request->getAttribute(UserInterface::class);

        if (isset($_data['id'])) {
            $entity = $this->mm()
                ->with('user')
                ->where(['@this.id' => $_data['id'], '@this.user.phone' => $user->getIdentity()])
                ->one();

            if (is_null($entity)) {
                return null;
            }

            foreach ($_data as $key => $value) {
                $entity[$key] = $value;
            }

            return $entity;
        }

        $user = $this->mm('SM:User')->where(['@this.phone' => $user->getIdentity()])->one();
        $_data['idUser'] = $user->id;
        return $this->mm($_data);
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
