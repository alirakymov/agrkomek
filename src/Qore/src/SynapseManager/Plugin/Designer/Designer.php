<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Plugin\Designer;

use Psr\Http\Message\ServerRequestInterface;
use Qore\DealingManager\DealingManager;
use Qore\InterfaceGateway\Component\ComponentInterface;
use Qore\InterfaceGateway\InterfaceGateway;
use Qore\ORM\Entity\EntityInterface;
use Qore\SynapseManager\Artificer\ArtificerInterface;
use Qore\SynapseManager\Plugin\Chain\Chain;
use Qore\SynapseManager\Plugin\Designer\InterfaceGateway\DesignerComponent;
use Qore\SynapseManager\Plugin\PluginInterface;
use Qore\SynapseManager\SynapseManager;

class Designer implements PluginInterface
{
    /**
     * @var string
     */
    const EDITOR = 'editor';

    /**
     * @var string
     */
    const VIEWER = 'viewer';

    /**
     * @var SynapseManager
     */
    private $_sm;

    /**
     * @var ArtificerInterface
     */
    private $_artificer;

    /**
     * @var DealingManager
     */
    private $_dm;

    /**
     * @var ServerRequestInterface
     */
    private $_request;

    /**
     * @var EntityInterface
     */
    private $_canvas;

    /**
     * @var string
     */
    private $_purpose;

    /**
     * @var InterfaceGateway
     */
    private $_ig;

    /**
     * @var string
     */
    private $_actionUri;

    /**
     * Constructor
     *
     * @param \Qore\DealingManager\DealingManager $_dm
     */
    public function __construct(DealingManager $_dm, InterfaceGateway $_ig)
    {
        $this->_dm = $_dm;
        $this->_ig = $_ig;
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
     * @param \Qore\SynapseManager\Artificer\ArtificerInterface $_artificer
     *
     * @return void
     */
    public function setArtificer(ArtificerInterface $_artificer): void
    {
        $this->_artificer = $_artificer;
    }

    /**
     * Set canvas object
     *
     * @param \Qore\ORM\Entity\EntityInterface $_object
     *
     * @return Designer
     */
    public function setCanvas(EntityInterface $_object) : Designer
    {
        $this->_canvas = $_object;
        return $this;
    }

    /**
     * Set action uri for any actions of this plugin and sub blocks
     *
     * @param string $_actionUri
     *
     * @return Designer
     */
    public function setActionUri(string $_actionUri) : Designer
    {
        $this->_actionUri = $_actionUri;
        return $this;
    }

    /**
     * Set purpose for desig
     *
     * @param string $_purpose
     *
     * @return Designer
     */
    public function setPurpose(string $_purpose) : Designer
    {
        $this->_purpose = $_purpose;
        return $this;
    }

    /**
     * process
     *
     * @return mixed
     */
    public function process(string $_purpose)
    {
        $requestModel = $this->_artificer->getModel();
        $request = $requestModel->getRequest();

        $ig = $this->_ig;
        /** @var DesignerComponent */
        $designerComponent = $ig(DesignerComponent::class, sprintf(
            'designer-%s-%s',
            strtolower($this->_artificer->getNameIdentifier()),
            $this->_canvas['id'])
        );

        $model = (new Model())
            ->setRequest($requestModel->getRequest())
            ->setCanvas($this->_canvas)
            ->setActionUri($this->_actionUri)
            ->setDesignerComponent($designerComponent);

        $designerComponent->setModel($model);

        $_purpose === static::EDITOR && $model->isEditor(true);
        $_purpose === static::VIEWER && $model->isViewer(true);

        /** @var Chain */
        $chainPlugin = $this->_artificer->plugin(Chain::class);
        $chain = $chainPlugin->build(Handler::class, 'Plugin\\Designer\\Handler', false);

        # - Initialize canvas
        $this->initialize($chain, $model);
        # - Handle request
        $handleResult = $this->handle($chain, $model);
        # - Set block panel actions
        $designerComponent->setBlockPanel($model('designer-options')('actions'));
        # - Set action uri for any reaction from interface
        $designerComponent->setActionUri($this->_actionUri);
        # - Return main component with components from result of launched chain
        return array_merge([$designerComponent], $handleResult);
    }

    /**
     * Initialize canvas instance
     *
     * @param array $_chain
     * @param ModelInterface $_model
     *
     * @return void
     */
    protected function initialize(array $_chain, ModelInterface $_model) : void
    {
        $_model->isInitialize(true);

        $dm = $this->_dm;
        $dm(function($_builder) use ($_chain) {
            foreach ($_chain as $clause) {
                $_builder($clause);
            }
        })->launch($_model);
    }

    /**
     * Handle request and return result as array of merged components list
     *
     * @param array $_chain
     * @param ModelInterface $_model
     *
     * @return array
     */
    protected function handle(array $_chain, ModelInterface $_model) : array
    {
        $_model->isHandle(true);

        $dm = $this->_dm;
        $handleResult = $dm(function($_builder) use ($_chain) {
            foreach ($_chain as $clause) {
                $_builder($clause);
            }
        })->launch($_model);

        # - Save canvas if action is save
        $_model->isSave() && $this->_sm->mm($_model->getCanvas())->save();
        $_model->getCanvas()->flushEvents();

        return (array)$handleResult;
    }

}
