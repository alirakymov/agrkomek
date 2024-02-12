<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\System\API\Executor;

use Qore\Qore as Qore;
use Qore\App\SynapseNodes;
use Qore\Router\RouteCollector;
use Qore\DealingManager;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Qore\QueueManager\QueueManager;
use Qore\App\SynapseNodes\Components\Product\Jobs\UpdateQuantityJob;
use Qore\SynapseManager\Artificer\Service\ServiceArtificer;

/**
 * Class: APIService
 *
 * @see SynapseNodes\BaseManagerServiceArtificer
 */
class APIService extends ServiceArtificer
{
    const STATUS_SUCCESS = 0;
    const STATUS_MISSING_PRODUCTS = 11;
    const STATUS_NO_PRODUCTS = 12;

    /**
     * serviceForm
     *
     * @var string
     */
    private $serviceForm = '';

    /**
     * routes
     *
     * @param RouteCollector $_router
     */
    public function routes(RouteCollector $_router) : void
    {
        $_router->group('/api', null, function($_router) {
            $_router->post('/sync-quantities', 'sync-quantities');
        });
        # - Register related subjects routes
        $this->registerSubjectsRoutes($_router);
        # - Register this subject forms routes
        $this->registerFormsRoutes($_router);
    }

    /**
     * compile
     *
     */
    public function compile() : ?DealingManager\ResultInterface
    {
        $routeResult = $this->model->getRouteResult();

        switch (true) {
            case $routeResult->getMatchedRouteName() === $this->getRouteName('sync-quantities'):
                return $this->syncQuantities();
            default:
                return $this->response(new HtmlResponse('Not Found', 404));
        }

        return null;
    }

    /**
     * Index method for a-p-i
     *
     * @return ?DealingManager\ResultInterface
     */
    protected function index() : ?DealingManager\ResultInterface
    {
        return $this->response(new HtmlResponse('Hi from Qore\App\SynapseNodes\System\API\Executor - APIService'));
    }

    /**
     * Synchronization quantities in products method for API
     *
     * @return ?DealingManager\ResultInterface
     */
    protected function syncQuantities() : ?DealingManager\ResultInterface
    {
        $request = $this->model->getRequest();
        if (! isset($request->getUploadedFiles()['products']) || ! $request->getUploadedFiles()['products']->getSize()) {
            return $this->response(new JsonResponse(['message' => 'no products found'],400));
        }
        $uploadedFile = $request->getUploadedFiles()['products'];
        $fileFullPath = Qore::config('catalog.syncfiles-dir') . '/' . uniqid() . '.' . $uploadedFile->getClientFilename();
        $uploadedFile->moveTo($fileFullPath);

        Qore::service(QueueManager::class)->publish(new UpdateQuantityJob([
            'fileFullPath' => $fileFullPath,
        ]));

        return $this->response(new JsonResponse([],200));
    }
}
