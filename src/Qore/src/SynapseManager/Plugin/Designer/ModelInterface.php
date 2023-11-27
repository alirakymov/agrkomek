<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Plugin\Designer;

use Psr\Http\Message\ServerRequestInterface;
use Qore\ORM\Entity\EntityInterface;
use Qore\SynapseManager\Plugin\Chain\ModelInterface as ChainModelInterface;
use Qore\SynapseManager\Plugin\Designer\InterfaceGateway\DesignerComponent;

interface ModelInterface extends ChainModelInterface
{
    /**
     * @var string
     */
    const CHAIN_PURPOSE = 'chain-purpose';

    /**
     * @var string
     */
    const CHAIN_PURPOSE_EDITOR = 'editor';

    /**
     * @var string
     */
    const CHAIN_PURPOSE_VIEWER = 'viewer';

    /**
     * @var string
     */
    const CHAIN_LAUNCH = 'chain-launch-purpose';

    /**
     * @var string
     */
    const CHAIN_LAUNCH_INITIALIZE = 'initialize';

    /**
     * @var string
     */
    const CHAIN_LAUNCH_HANDLE = 'handle';

    /**
     * Set action options to current chain point
     *
     * @param array $_options
     *
     * @return Model
     */
    public function setAction(array $_options) : Model;

    /**
     * Set canvas
     *
     * @param CanvasEntity $_canvas
     *
     * @return Model
     */
    public function setCanvas(CanvasEntity $_canvas) : Model;

    /**
     * Get canvas object
     *
     * @return CanvasEntity
     */
    public function getCanvas() : CanvasEntity;

    /**
     * Set request server instance
     *
     * @param ServerRequestInterface $_request
     *
     * @return Model
     */
    public function setRequest(ServerRequestInterface $_request) : Model;

    /**
     * Get request server instance
     *
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    public function getRequest() : ServerRequestInterface;

    /**
     * Set action uri for share it to any sub blocks in chain
     *
     * @param string $_actionUri
     *
     * @return Model
     */
    public function setActionUri(string $_actionUri) : Model;

    /**
     * Return uri for designer actions
     *
     * @return string|null
     */
    public function getActionUri() : ?string;

    /**
     * Set DesignerComponent instance
     *
     * @param \Qore\SynapseManager\Plugin\Designer\InterfaceGateway\DesignerComponent $_dc
     *
     * @return Model
     */
    public function setDesignerComponent(DesignerComponent $_dc) : Model;

    /**
     * Get DesignerComponent
     *
     * @return DesignerComponent
     */
    public function getDesignerComponent() : ?DesignerComponent;

    /**
     * Match data for current path form given params
     *
     * @param Iterable $_params
     *
     * @return array
     */
    public function matchParams(Iterable $_params) : array;

    /**
     * Get objects for current blocks from canvas state
     *
     * @return array
     */
    public function getCurrentBlocks() : array;

    /**
     * Set/Check for chain purpose
     *
     * @param bool $_mapping (optional)
     *
     * @return bool|Model
     */
    public function isEditor(bool $_editor = null);

    /**
     * Set/Check chain purpose
     *
     * @param bool $_viewer (optional)
     *
     * @return bool|Model
     */
    public function isViewer(bool $_viewer = null);

    /**
     * Set/Check chain launch purpose
     *
     * @param bool $_bool (optional)
     *
     * @return bool|Model
     */
    public function isInitialize(bool $_bool = null);

    /**
     * Set/Check chain launch purpose
     *
     * @param bool $_bool (optional)
     *
     * @return bool|Model
     */
    public function isHandle(bool $_bool = null);

    /**
     * Check current request for compose canvas action
     *
     * @return bool
     */
    public function isCompose() : bool;

    /**
     * Check current request for reaction from interface
     *
     * @return bool
     */
    public function isReaction() : bool;

    /**
     * Check current request for save action
     *
     * @return bool
     */
    public function isSave() : bool;

}
