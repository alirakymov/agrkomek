<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Plugin\Filter;

use Psr\Http\Message\ServerRequestInterface;
use Qore\Collection\CollectionInterface;
use Qore\Form\FormManagerInterface;
use Qore\SynapseManager\Structure\Entity\SynapseService;
use Qore\SynapseManager\SynapseManager;

interface ModelInterface
{
    /**
     * Generate new Or return exists state object
     *
     * @param string $_index
     *
     * @return Model
     */
    public function getState(string $_index) : ModelInterface;

    /**
     * Return synapse service chain
     *
     * @return \Qore\Collection\CollectionInterface
     */
    public function getServiceCollection() : CollectionInterface;

    /**
     * Set synapse service chain
     *
     * @param \Qore\Collection\CollectionInterface $_serviceCollection
     *
     * @return void
     */
    public function setServiceCollection(CollectionInterface $_serviceCollection) : void;

    /**
     * Get last service
     *
     * @return \Qore\SynapseManager\Structure\Entity\SynapseService
     */
    public function getLastService(): SynapseService;

    /**
     * Set/Check chain purpose
     *
     * @param bool $_build (optional) 
     *
     * @return ModelInterface|bool 
     */
    public function isBuild(bool $_build = null);

    /**
     * Set form manager
     *
     * @param \Qore\Form\FormManagerInterface $_form
     *
     * @return ModelInterface [TODO:description]
     */
    public function setForm(FormManagerInterface $_form): ModelInterface;

    /**
     * Get form manger
     *
     * @return \Qore\Form\FormManagerInterface|null
     */
    public function getForm(): ?FormManagerInterface;

    /**
     * Set request
     *
     * @param \Psr\Http\Message\ServerRequestInterface $_request 
     *
     * @return ModelInterface 
     */
    public function setRequest(ServerRequestInterface $_request): ModelInterface;

    /**
     * Get request
     *
     * @return \Psr\Http\Message\ServerRequestInterface|null
     */
    public function getRequest(): ?ServerRequestInterface;

    /**
     * Set synapse manager 
     *
     * @param SynapseManager $_sm
     *
     * @return ModelInterface 
     */
    public function setSynapseManager(SynapseManager $_sm): ModelInterface;

    /**
     * Get synapse manager
     *
     * @return SynapseManager 
     */
    public function getSynapseManager(): SynapseManager;

    /**
     * Set filters
     *
     * @param \Qore\Collection\CollectionInterface $_filters 
     *
     * @return ModelInterface
     */
    public function setFilters(CollectionInterface $_filters): ModelInterface;

    /**
     *  Get current filters
     *
     * @return array 
     */
    public function getCurrentFilters(): array;
}
