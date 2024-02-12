<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\System\PageComponent\MainPage;

use Qore\App\SynapseNodes\Components\Category\CategoryRepository;
use Qore\App\SynapseNodes\Components\Product\ProductRepository;
use Qore\Qore;
use Qore\App\SynapseNodes;
use Qore\Router\RouteCollector;
use Qore\DealingManager;
use Qore\SynapseManager\Artificer\Service\ServiceArtificer;

/**
 * Class: PageComponentService
 *
 * @see SynapseNodes\BaseManagerServiceArtificer
 */
class PageComponentService extends ServiceArtificer
{
    /**
     * serviceForm
     *
     * @var string
     */
    private $serviceForm = 'PageComponent';

    /**
     * routes
     *
     * @param RouteCollector $_router
     */
    public function routes(RouteCollector $_router) : void
    {
        $_router->group('{page:[a-z0-9\-]*}', null, function($_router) {
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
        if (! isset($this->model['page'])) {
            return null;
        }

        $productRepository = Qore::service(CategoryRepository::class);
        $categories = $productRepository->generateUrls($this->mm('SM:Category')->all())->nest('id', '__idparent');

        $this->model->page->setComponentData('categories', $categories);

        switch (true) {
            default:
                return $this->catalogAll();
        }

        return null;
    }

    /**
     * Populate with data of catalog
     *
     * @return void
     */
    private function catalogAll() : void
    {
        $products = $this->mm('SM:Product')->select(function($_select) {
            $_select->order('@this.price asc')
                // ->offset($offset)
                ->limit($this->model->page->component()->catalogCategoryProductCount ?? 12)
                ->group('@this.id');
            })->with('category')->where(function($_where) {
                $_where(['@this.category.id' => 1]);
                $_where->greaterThan('@this.quantity', 0);
            })->with('category')->all();

        $productRepository = Qore::service(ProductRepository::class);
        $productRepository->collectAttributesForProducts($products);
        $productRepository->generateUrls($products);
        $productRepository->loadImages($products);

        $this->model->page->setComponentData('products', $products);
    }

}
