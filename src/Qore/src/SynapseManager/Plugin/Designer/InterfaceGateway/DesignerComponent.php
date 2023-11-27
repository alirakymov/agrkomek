<?php

namespace Qore\SynapseManager\Plugin\Designer\InterfaceGateway;

use Qore\InterfaceGateway\Component\AbstractComponent;
use Qore\InterfaceGateway\Component\ComponentInterface;
use Qore\SynapseManager\Plugin\Designer\CanvasState;
use Qore\SynapseManager\Plugin\Designer\ComposableEntityInterface;
use Qore\SynapseManager\Plugin\Designer\ModelInterface;
use Ramsey\Uuid\Uuid;

class DesignerComponent extends AbstractComponent
{
    /**
     * @var string - uri for designer actions
     */
    const ACTION_URI = 'designer-action-uri';

    /**
     * @var string
     */
    protected $type = 'qc-designer';

    /**
     * @var array
     */
    private array $blocks = [];

    /**
     * @var ModelInterface
     */
    private $_model;

    /**
     * Set ModelInterface instance
     *
     * @param \Qore\SynapseManager\Plugin\Designer\ModelInterface $_model
     *
     * @return DesignerComponent
     */
    public function setModel(ModelInterface $_model) : DesignerComponent
    {
        $this->_model = $_model;
        return $this;
    }

    /**
     * Set elements to block panel
     *
     * @param Iterable $_blockPanelActions
     *
     * @return DesignerComponent
     */
    public function setBlockPanel($_blockPanelActions) : DesignerComponent
    {
        $options = (array)$_blockPanelActions;
        $this->setOption('block-panel', (array)$_blockPanelActions);

        return $this;
    }

    /**
     * Set action uri for any interactive actions from interface
     *
     * @param string $_actionUri
     *
     * @return DesignerComponent
     */
    public function setActionUri(string $_actionUri) : DesignerComponent
    {
        $this->setOption('action-uri', $_actionUri);
        return $this;
    }

    /**
     * Register block
     *
     * @param \Qore\SynapseManager\Plugin\Designer\ComposableEntityInterface $_entity
     * @param ComponentInterface $_component
     *
     * @return DesignerComponent
     */
    public function register(ComposableEntityInterface $_entity, ComponentInterface $_component) : DesignerComponent
    {
        $blockIndex = sprintf('%s#%s', $this->_model->getPath(), $this->getBlockId($_entity));
        $this->blocks[$blockIndex] = $this->wrapToBlockComponent($_entity, $_component);
        return $this;
    }

    /**
     * Wrap InterfaceGateway component to description block wrapper
     *
     * @param \Qore\SynapseManager\Plugin\Designer\ComposableEntityInterface $_entity
     * @param \Qore\InterfaceGateway\Component\ComponentInterface $_component
     *
     * @return WrapperInterface
     */
    public function wrapToBlockComponent(ComposableEntityInterface $_entity, ComponentInterface $_component): WrapperInterface
    {
        $_component = ($this->ig)(
            WrapperComponent::class,
            sprintf('designer-block.%s', $this->getBlockId($_entity))
        )->component($_component)->strategy(ComponentInterface::REPLACE);

        # - Set designer block options
        $_component->setOption(CanvasState::BLOCK_ID, $_entity->getUnique());
        $_component->setOption(CanvasState::ENDPOINT, $this->_model->getPath());
        $_component->setOption(static::ACTION_URI, $this->_model->getActionUri());

        return $_component;
    }

    /**
     * Wrap InterfaceGateway component to designer wrapper
     *
     * @param \Qore\SynapseManager\Plugin\Designer\ComposableEntityInterface $_entity
     * @param \Qore\InterfaceGateway\Component\ComponentInterface|string|null $_component (optional)
     *
     * @return WrapperComponent
     */
    public function wrapComponent(ComposableEntityInterface $_entity, $_component = null): WrapperInterface
    {
        # - Create wrapper component if is null
        $_component ??= ($this->ig)(
            WrapperComponent::class,
            sprintf('wrapper.%s.%s', get_class($_entity), $_entity['id'])
        );
        # - Component is string
        if (is_string($_component)) {
            $_component = ($this->ig)(
                WrapperComponent::class,
                $_component
            );
        # - Wrap interface gateway component
        } elseif (! $_component instanceof WrapperInterface) {
            $_component = ($this->ig)(
                WrapperComponent::class,
                sprintf('wrapper.%s', $_component->getName())
            )->component($_component);
        }

        # - Set designer block options
        $_component->setOption(CanvasState::BLOCK_ID,$_entity->getUnique());
        $_component->setOption(CanvasState::ENDPOINT, $this->_model->getPath());
        $_component->setOption(static::ACTION_URI, $this->_model->getActionUri());

        return $_component;
    }

    /**
     * Compose designer component
     *
     * @return array
     */
    public function compose() : array
    {
        # - If is editor mode set component type
        $this->_model->isEditor() && $this->setType('qc-designer-editor');
        # - If is viewer mode set component type
        $this->_model->isViewer() && $this->setType('qc-designer-viewer');

        if ($this->_model->isEditor()) {
            # - Set actions
            $this->setActions(array_merge([
                'save' => [
                    'label' => 'Сохранить',
                    'icon' => 'fas fa-save',
                    'actionUri' => 'action:save',
                ]
            ], $this->getActions()));
        }

        $canvasState = $this->_model->getCanvas()->getCanvasState();
        foreach (($canvasState['blocks'] ?? []) as $block) {
            if (! isset($block['endpoint'], $block[CanvasState::BLOCK_ID])) {
                continue;
            }
            $blockIndex = sprintf('%s#%s', $block['endpoint'], $block[CanvasState::BLOCK_ID]);
            if (isset($this->blocks[$blockIndex])) {
                $this->component($this->blocks[$blockIndex]);
                unset($this->blocks[$blockIndex]);
            }
        }

        # - Combine all components to execute command
        foreach ($this->blocks as $block) {
            $this->component($block);
        }

        # - Register add components command
        return parent::compose();
    }

    /**
     * Initialize and get Block Indentifer
     *
     * @param \Qore\SynapseManager\Plugin\Designer\ComposableEntityInterface $_entity
     *
     * @return string
     */
    protected function getBlockId(ComposableEntityInterface $_entity): string
    {
        # - Generate unique block-id if block is new
        return $_entity->getUnique() ?? $_entity->setUnique(Uuid::uuid4())->getUnique();
    }

}
