<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Plugin\Designer;

use ArrayIterator;
use ArrayObject;
use DateTime;
use Psr\Http\Message\ServerRequestInterface;
use Qore\ORM\Entity\EntityInterface;
use Qore\SynapseManager\Plugin\Chain\Model as ChainModel;
use Qore\Collection\Collection;
use Qore\Collection\CollectionInterface;
use Qore\SynapseManager\Plugin\Designer\InterfaceGateway\DesignerComponent;
use Qore\SynapseManager\Structure\Entity\SynapseService;

class Model extends ChainModel implements ModelInterface
{
    /**
     * Set request server instance
     *
     * @param ServerRequestInterface $_request
     *
     * @return Model
     */
    public function setRequest(ServerRequestInterface $_request) : Model
    {
        $this['request'] = $_request;
        return $this;
    }

    /**
     * Get request server instance
     *
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    public function getRequest() : ServerRequestInterface
    {
        return $this['request'];
    }

    /**
     * Set canvas
     *
     * @param CanvasEntity $_canvas
     *
     * @return Model
     */
    public function setCanvas(CanvasEntity $_canvas) : Model
    {
        $this['canvas'] = $_canvas;
        return $this;
    }

    /**
     * Get canvas object
     *
     * @return CanvasEntity
     */
    public function getCanvas() : CanvasEntity
    {
        return $this['canvas'];
    }

    /**
     * Set/Check for chain purpose
     *
     * @param bool $_mapping (optional)
     *
     * @return bool|Model
     */
    public function isEditor(bool $_editor = null)
    {
        if (is_null($_editor)) {
            return isset($this[$this::CHAIN_PURPOSE]) && $this[$this::CHAIN_PURPOSE] === $this::CHAIN_PURPOSE_EDITOR;
        }

        $this[$this::CHAIN_PURPOSE] = $_editor ? $this::CHAIN_PURPOSE_EDITOR : null;
        return $this;
    }

    /**
     * Set/Check chain purpose
     *
     * @param bool $_viewer (optional)
     *
     * @return bool|Model
     */
    public function isViewer(bool $_viewer = null)
    {
        if (is_null($_viewer)) {
            return isset($this[$this::CHAIN_PURPOSE]) && $this[$this::CHAIN_PURPOSE] === $this::CHAIN_PURPOSE_VIEWER;
        }

        $this[$this::CHAIN_PURPOSE] = $_viewer ? $this::CHAIN_PURPOSE_VIEWER : null;
        return $this;
    }

    /**
     * Set/Check chain launch purpose
     *
     * @param bool $_bool (optional)
     *
     * @return bool|Model
     */
    public function isInitialize(bool $_bool = null)
    {
        if (is_null($_bool)) {
            return isset($this[$this::CHAIN_LAUNCH]) && $this[$this::CHAIN_LAUNCH] === $this::CHAIN_LAUNCH_INITIALIZE;
        }

        $this[$this::CHAIN_LAUNCH] = $_bool ? $this::CHAIN_LAUNCH_INITIALIZE : null;
        return $this;
    }

    /**
     * Set/Check chain launch purpose
     *
     * @param bool $_bool (optional)
     *
     * @return bool|Model
     */
    public function isHandle(bool $_bool = null)
    {
        if (is_null($_bool)) {
            return isset($this[$this::CHAIN_LAUNCH]) && $this[$this::CHAIN_LAUNCH] === $this::CHAIN_LAUNCH_HANDLE;
        }

        $this[$this::CHAIN_LAUNCH] = $_bool ? $this::CHAIN_LAUNCH_HANDLE : null;
        return $this;
    }

    /**
     * Check current request for compose canvas action
     *
     * @return bool
     */
    public function isCompose() : bool
    {
        $request = $this->getRequest();
        return $request->getMethod() === 'GET';
    }

    /**
     * Check current request for reaction from interface
     *
     * @return bool
     */
    public function isReaction() : bool
    {
        $request = $this->getRequest();
        $params = $request->getParsedBody();
        return $request->getMethod() === 'POST'
            && (! isset($params['endpoint']) || $params['endpoint'] !== 'action:save')
            && $this->matchParams($params['data'] ?? []);
    }

    /**
     * Check current request for save action
     *
     * @return bool
     */
    public function isSave() : bool
    {
        $request = $this->getRequest();
        $params = $request->getParsedBody();
        return $request->getMethod() === 'POST' && isset($params['endpoint']) && $params['endpoint'] === 'action:save';
    }

    /**
     * Set control options to current chain point
     *
     * @param array $_options
     *
     * @return Model
     */
    public function setAction(array $_options) : Model
    {
        $state = $this('designer-options')('actions');
        $state[$this->getPath()] = $_options;
        return $this;
    }

    /**
     * Set action uri for share it to any sub blocks in chain
     *
     * @param string $_actionUri
     *
     * @return Model
     */
    public function setActionUri(string $_actionUri) : Model
    {
        $state = $this('designer-options');
        $state['actionUri'] = $_actionUri;
        return $this;
    }

    /**
     * Return uri for designer actions
     *
     * @return string|null
     */
    public function getActionUri() : ?string
    {
        return $this('designer-options')['actionUri'] ?? null;
    }

    /**
     * Set DesignerComponent instance
     *
     * @param \Qore\SynapseManager\Plugin\Designer\InterfaceGateway\DesignerComponent $_dc
     *
     * @return Model
     */
    public function setDesignerComponent(DesignerComponent $_dc) : Model
    {
        $this['designer-component'] = $_dc;
        return $this;
    }

    /**
     * Get DesignerComponent
     *
     * @return DesignerComponent
     */
    public function getDesignerComponent() : ?DesignerComponent
    {
        return $this['designer-component'] ?? null;
    }

    /**
     * Match data for current path form given params
     *
     * @param Iterable $_params
     *
     * @return array
     */
    public function matchParams(Iterable $_params) : array
    {
        $result = [];
        foreach ($_params as $param) {
            if (is_array($param) && isset($param['endpoint']) && $param['endpoint'] === $this->getPath()) {
                $result[] = $param;
            }
        }
        return $result;
    }

    /**
     * Get objects for current blocks from canvas state
     *
     * @return array
     */
    public function getCurrentBlocks() : array
    {
        return $this->getCanvas()->getCanvasState()->findAll($this->getPath());
    }

}
