<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\ImageStore\ApiPrivate;

use Qore\DealingManager\ResultInterface;
use Qore\Router\RouteCollector;
use Laminas\Diactoros\Response\JsonResponse;
use Qore\SynapseManager\Artificer\Service\ServiceArtificer;
use Qore\SynapseManager\Plugin\RoutingHelper\RoutingHelper;

/**
 * Class: ImageStoreService
 *
 * @see Qore\SynapseManager\Artificer\Service\ServiceArtificer
 */
class ImageStoreService extends ServiceArtificer
{
    /**
     * sortable
     *
     * @var mixed
     */
    private $sortable = false;

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
        $_router->group('/image', null, function($_router) {
            $_router->post('/upload', 'upload');
        });
        # - Register related subjects routes
        $this->registerSubjectsRoutes($_router);
    }

    /**
     * compile
     *
     */
    public function compile() : ?ResultInterface
    {
        /** @var RoutingHelper */
        $this->routingHelper = $this->plugin(RoutingHelper::class);
        list($method, $arguments) = $this->routingHelper->dispatch(['upload']) ?? [null, null];

        return ! is_null($method) ? call_user_func_array([$this, $method], $arguments ?? []) : null;
    }

    /**
     * Image upload
     *
     * @return \Qore\DealingManager\ResultInterface
     */
    protected function upload(): ResultInterface
    {
        # - Upload files
        $files = $this->model->getRequest()->getUploadedFiles();

        $file = $files['image'] ?? $files['file'] ?? null;

        if (! is_null($file)) {
            $this->mm($image = $this->mm(['file' => $file]))->save();
            # - Generate json response
            return $this->response(new JsonResponse([
                'success' => 1,
                'file' => [
                    'url' => $image->imageUrl,
                ]
            ]));
        }

        return $this->response(new JsonResponse([
            'success' => false,
        ]));

    }

}
