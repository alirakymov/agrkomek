<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Plugin\FormMaker;

use Psr\Http\Message\ServerRequestInterface;
use Qore\Form\FormManager;
use Qore\Qore;
use Qore\Router\RouteCollector;
use Qore\SynapseManager\Artificer\ArtificerInterface;
use Qore\SynapseManager\Artificer\Form\DataSource\DataSourceInterface;
use Qore\SynapseManager\Artificer\RequestModel;
use Qore\SynapseManager\Artificer\Service\ServiceArtificerInterface;
use Qore\SynapseManager\Plugin\PluginInterface;
use Qore\SynapseManager\SynapseManager;

class FormMaker implements PluginInterface
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
     * @var bool
     */
    private $withoutSubmit = false;

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
    public function setSynapseManager(SynapseManager $_sm) : void
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
    public function setArtificer(ArtificerInterface $_artificer) : void
    {
        $this->_artificer = $_artificer;
    }

    /**
     * Mount form without submit field
     *
     * @param bool $_bool
     *
     * @return FormMaker
     */
    public function withoutSubmit(bool $_bool = true) : FormMaker
    {
        $this->withoutSubmit = $_bool;
        return $this;
    }

    /**
     * Make form manager with $_formName
     * $_dataSource mixed argument can be:
     *  - DataSourceInterface - generated DataSourceInterface instance for fill current form
     *  - auto - automatically detect and build DataSourceInterface instance for fill current form
     *  - null - no fill current form
     *
     * @param string $_formName
     * @param \Psr\Http\Message\ServerRequestInterface $_request (optional)
     * @param \Qore\SynapseManager\Artificer\Form\DataSource\DataSourceInterface|string|null $_dataSource (optional)
     *
     * @return \Qore\Form\FormManager
     */
    public function make(string $_formName, ServerRequestInterface $_request = null, $_dataSource = 'auto') : FormManager
    {
        # - Get form artificer
        $formArtificer = $this->_artificer->getFormArtificer($_formName);

        if (is_null($formArtificer)) {
            throw new Exception(sprintf(
                'Undefined form which name is %s:%s#%s',
                $this->_artificer->getEntity()->synapse()->name,
                $this->_artificer->getEntity()->name,
                $_formName
            ));
        }

        if (! is_null($_request)) {
            $model = (new RequestModel())->setRequest($_request);
        } else {
            $model = $this->_artificer->getModel();
        }

        # - If method is POST make data source from request data
        $request = $model->getRequest();

        if (is_null($request)) {
            throw new Exception(sprintf('Undefined request instance for make %s form', $_formName));
        }

        # - Get route result
        $routeResult = $model->getRouteResult();
        $routeParams = $routeResult->getMatchedParams();
        # - Detect and build DataSourceInterface instance
        if ($_dataSource === 'auto') {
            if ($request->getMethod() === 'POST') {
                $_dataSource = $this->_artificer->getRequestDataSource($request, $formArtificer);
            } elseif (isset($routeParams['id'])) {
                $data = $this->_artificer->gateway()->where(function($_where) use ($routeParams) {
                    $_where(['@this.id' => $routeParams['id']]);
                })->all();
                $_dataSource = $this->_artificer->getEntityDataSource($data);
            } else {
                $_dataSource = null;
            }
        }
        # - Set DataSourceInterface instance
        ! is_null($_dataSource) && $model->setDataSource($_dataSource);

        # - Build form without submit field
        $model['withoutSubmit'] = $this->withoutSubmit;
        # - Generate form by this service
        $formArtificer->dm()->launch($model->initOnly(false));

        return $model->getFormManager()
            ->setAction(
                Qore::url(
                    $routeResult->getMatchedRouteName(),
                    $routeResult->getMatchedParams(),
                    $request->getQueryParams(),
                )
            );
    }

}
