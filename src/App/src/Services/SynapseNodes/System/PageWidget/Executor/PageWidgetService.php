<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\System\PageWidget\Executor;

use Qore\DealingManager\ResultInterface;
use Qore\Qore;
use Qore\Router\RouteCollector;
use Qore\SynapseManager\Artificer\Decorator\ListComponent;
use Qore\SynapseManager\Artificer\Service;
use Qore\Debug\DebugBar;
use Mezzio\Helper\UrlHelper;
use Mezzio\Template\TemplateRendererInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Qore\SynapseManager\Artificer\Service\ServiceArtificer;

/**
 * Class: PageComponentService
 *
 * @see SynapseNodes\BaseManagerServiceArtificer
 */
class PageWidgetService extends ServiceArtificer
{
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
    }

    /**
     * compile
     *
     */
    public function compile() : ?ResultInterface
    {
        if (! $page = $this->model['page'] ?? null) {
            return null;
        }

        $widgets = $page->widgets()->filter(function($_widget) {
            return ! $_widget->isSystemService();
        })->groupBy('service');

        foreach ($widgets as $widget => $items) {
            $this->model['widgetPacket'] = $items;
            $this->dm($widget)->launch($this->model);
        }


        $widgetsOrder = $this->combineSortOrders($page);
        $page->widgets = $page->widgets()->sortBy(function($_item) use ($widgetsOrder){
            return (int)array_search($_item->id, array_values($widgetsOrder));
        }, SORT_ASC);
        $page->widgets = $page->widgets()->nest('id', '__idparent', 'children');
        $page['structuredWidgetsData'] = $this->recursiveCombineWidgets($page->widgets());

        return null;
    }

    private function combineSortOrders($_page)
    {
        $optionName = $this->sm('PageWidget:Manager')->getOrderOptionName();
        $orderList = $_page['__options'][$optionName] ?? [];

        foreach ($_page->widgets() as $widget) {
            $orderList = array_merge($orderList, $widget['__options'][$optionName] ?? []);
        }

        return $orderList;
    }

    private function recursiveCombineWidgets($_widgets)
    {
        $structuredWidgets = [];
        foreach ($_widgets as $widget) {
            $structuredWidgets[$widget->name] = $widget;
            if (isset($widget['children'])) {
                $widget['children'] = $this->recursiveCombineWidgets($widget['children']);
            }
        }
        return $structuredWidgets;
    }

}
