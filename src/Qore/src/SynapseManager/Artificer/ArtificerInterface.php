<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Artificer;

use Qore\DealingManager;
use Qore\DealingManager\DealingManager as QoreDealingManager;
use Qore\DealingManager\Result;
use Qore\DealingManager\ResultInterface;
use Qore\DealingManager\ScenarioBuilder;
use Qore\ORM\Entity\EntityInterface;
use Qore\ORM\Gateway\Gateway;
use Psr\Http\Message\ServerRequestInterface;
use Qore\SynapseManager\Artificer\Form\DataSource\EntityDataSource;
use Qore\SynapseManager\Artificer\Form\DataSource\RequestDataSource;
use Qore\SynapseManager\Artificer\Form\FormArtificerInterface;
use Qore\SynapseManager\Plugin\PluginInterface;
use Qore\SynapseManager\SynapseManager;

/**
 * Interface: ArtificerInterface
 *
 * @see DealingManager\ScenarioClauseInterface
 */
interface ArtificerInterface
{

    /**
     * GBL - preffix type which indicates global event
     */
    const GLB = 1;

    /**
     * LCL - preffix type which indicates local event
     */
    const LCL = 2;

    /**
     * clauses
     *
     * @param DealingManager\ScenarioBuilder $_bulider
     */
    public function clauses(DealingManager\ScenarioBuilder $_bulider) : void;

    /**
     * dispatch
     *
     */
    public function dispatch() : ?DealingManager\ResultInterface;

    /**
     * Get name identifier for current artificer (service or form)
     *  Service example: SynapseName:ServiceName
     *  Form example: SynapseName:ServiceName#FormName
     *
     * @return string
     */
    public function getNameIdentifier() : string;

    /**
     * getRequestModel
     *
     * @param ServerRequestInterface $_request
     */
    public function getRequestModel(ServerRequestInterface $_request) : RequestModel;

    /**
     * getResponseResult
     *
     * @param array $_data
     */
    public function getResponseResult(array $_data = []) : DealingManager\Result;

    /**
     * setSynpaseManager
     *
     * @param SynapseManager $_sm
     */
    public function setSynapseManager(SynapseManager $_sm) : void;

    /**
     * getSynapseManager
     *
     */
    public function getSynapseManager() : SynapseManager;

    /**
     * return service entity of synapse
     *
     * @return \Qore\ORM\Entity\EntityInterface
     */
    public function getEntity() : EntityInterface;

    /**
     * Return request model
     *
     * @return RequestModel|null
     */
    public function getModel() : ?RequestModel;

    /**
     * Generate RequestDataSource instance
     *
     * @param \Psr\Http\Message\ServerRequestInterface|null $_request (optional)
     * @param FormArtificerInterface|null $_artificer (optional)
     *
     * @return Form\DataSource\RequestDataSource
     */
    public function getRequestDataSource(ServerRequestInterface $request = null, FormArtificerInterface $formArtificer = null) : RequestDataSource;

    /**
     * Get gateway for current artificer
     *
     * @param array|null $_filters (optional)
     *
     * @return \Qore\ORM\Gateway\Gateway
     */
    public function gateway(array $_filters = null) : Gateway;

    /**
     * Generate EntityDataSource instance
     *
     * @param $_entities
     *
     * @return Form\DataSource\EntityDataSource
     */
    public function getEntityDataSource($_entities) : EntityDataSource;

    /**
     * Build DealingManager chain
     *
     * @param $_target (optional)
     * @param $_model (optional)
     *
     * @return Qore\DealingManager\DealingManager [TODO:description]
     */
    public function dm($_target = null, $_model = null) : QoreDealingManager;

    /**
     * Return created and initialized instance of requested plugin
     *
     * @param string $_name ClassName of plugin
     *
     * @return \Qore\SynapseManager\Plugin\PluginInterface
     */
    public function plugin(string $_name) : PluginInterface;

}
