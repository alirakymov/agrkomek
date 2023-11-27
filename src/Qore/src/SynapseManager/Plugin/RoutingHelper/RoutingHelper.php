<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Plugin\RoutingHelper;

use Psr\Http\Message\ServerRequestInterface;
use Qore\Router\RouteCollector;
use Qore\SynapseManager\Artificer\ArtificerInterface;
use Qore\SynapseManager\Artificer\Service\ServiceArtificerInterface;
use Qore\SynapseManager\Plugin\PluginInterface;
use Qore\SynapseManager\SynapseManager;

class RoutingHelper implements PluginInterface
{
    /**
     * @var SynapseManager
     */
    private $_sm;

    /**
     * @var ServiceArtificerInterface
     */
    private $_artificer;

    /**
     * Constructor
     *
     */
    public function __construct()
    {
    }

    /**
     * Set SynapseManager instance
     *
     * @param \Qore\SynapseManager\SynapseManager $_sm
     *
     * @return void
     */
    public function setSynapseManager(SynapseManager $_sm): void
    {
        $this->_sm = $_sm;
    }

    /**
     * Set Artificer instance
     *
     * @param \Qore\SynapseManager\Artificer\Service\ServiceArtificerInterface $_artificer
     *
     * @return void
     */
    public function setArtificer(ArtificerInterface $_artificer): void
    {
        $this->_artificer = $_artificer;
    }

    /**
     * routesCrud
     *
     * @param RouteCollector $_router
     */
    public function routesCrud(RouteCollector $_router)
    {
        $_router->any($this->indexRoute(), 'index');
        $_router->any($this->createRoute('/create'), 'create');
        $_router->any($this->reloadRoute('/reload'), 'reload');
        $_router->any($this->reorderRoute('/reorder'), 'reorder');
        $_router->any($this->updateRoute('/update/{id:\d+}'), 'update');
        $_router->any($this->deleteRoute('/delete/{id:\d+}'), 'delete');
        $_router->any($this->reportRoute('/report'), 'report');
        $_router->any($this->reportRoute('/report/{id:\d+}'), 'report-download');
        $_router->any($this->reportRoute('/report/remove/{id:\d+}'), 'report-remove');
    }

    /**
     * indexRoute
     *
     * @param string $_default
     */
    public function indexRoute(string $_default = '') : string
    {
        return $this->prepareDefaultRoute($_default, 'index');
    }

    /**
     * createRoute
     *
     * @param string $_default
     */
    public function createRoute(string $_default = '') : string
    {
        return $this->prepareDefaultRoute($_default, 'create');
    }

    /**
     * reloadRoute
     *
     * @param string $_default
     */
    public function reloadRoute(string $_default = '') : string
    {
        return $this->prepareDefaultRoute($_default, 'reload');
    }

    /** reorderRoute
     *
     * @param string $_default
     */
    public function reorderRoute(string $_default = '') : string
    {
        return $this->prepareDefaultRoute($_default, 'reorder');
    }

    /**
     * updateRoute
     *
     * @param string $_default
     */
    public function updateRoute(string $_default = '') : string
    {
        return $this->prepareDefaultRoute($_default, 'update');
    }

    /**
     * deleteRoute
     *
     * @param string $_default
     */
    public function deleteRoute(string $_default = '') : string
    {
        return $this->prepareDefaultRoute($_default, 'delete');
    }

    /**
     * Make report route
     *
     * @param string $_default
     */
    public function reportRoute(string $_default = '') : string
    {
        return $this->prepareDefaultRoute($_default, 'report');
    }

    /**
     * prepareDefaultRoute
     *
     * @param string $_default
     * @param string $_type
     */
    public function prepareDefaultRoute(string $_default, string $_type)
    {
        return $_default;
    }

    /**
     * dispathcRoute
     *
     * @param array (optional) $_actions
     *
     * @return array|null
     */
    public function dispatch(array $_actions = []) : ?array
    {
        $_actions = array_merge(['index', 'reload' => ['index', [true]], 'reorder', 'create', 'update', 'delete', 'report', 'report-download' => 'reportDownload', 'report-remove' => 'reportRemove'], $_actions);

        if (is_null($model = $this->_artificer->getModel())) {
            return null;
        }

        $routeResult = $model->getRouteResult();

        foreach ($_actions as $routeName => $action) {
            $routeName = is_int($routeName) ? $action : $routeName;
            $action = is_string($action) ? [$action] : $action;

            if (! isset($action[1])) {
                $action[] = null;
            }

            if ($routeResult->getMatchedRouteName() === $this->_artificer->getRouteName($routeName)) {
                return $action;
            }
        }

        return null;
    }

}
