<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\Weather\Api;

use GuzzleHttp\Client;
use Mezzio\Template\TemplateRendererInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Qore\DealingManager\ResultInterface;
use Qore\Qore;
use Qore\Router\RouteCollector;
use Qore\SynapseManager\Artificer\Service\ServiceArtificer;
use Qore\SynapseManager\Plugin\RoutingHelper\RoutingHelper;

/**
 * Class: ArticleService
 *
 * @see Qore\SynapseManager\Artificer\Service\ServiceArtificer
 */
class WeatherService extends ServiceArtificer
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
        $_router->group('/weather', null, function($_router) {
            $_router->get('', 'load');
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

        list($method, $arguments) = $this->routingHelper->dispatch(['load' => 'load']) ?? ['notFound', null];
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
    protected function load(): ?ResultInterface
    {
        $request = $this->model->getRequest();
        $queryParams = $request->getQueryParams();

        $client = new Client();

        $host = Qore::config('weather.host', 'https://api.weather.yandex.ru/v2/forecast/');
        $token = Qore::config('weather.token', '9a474249-cdb6-400d-9011-d117dcebada2');

        $response = $client->request('GET', $host, [
            'query' => [
                'lat' => $queryParams['lat'],
                'lon' => $queryParams['lon'],
                'lang' => $queryParams['lang'] ?? 'ru_RU',
            ],
            'headers' => [
                'X-Yandex-API-Key' => $token,
            ],
        ]);

        return $this->response(new JsonResponse(json_decode($response->getBody()->getContents(), true)));
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
