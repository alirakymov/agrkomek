<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Plugin\Chain;

use Qore\Collection\CollectionInterface;
use Qore\DealingManager\ModelInterface as DealingManagerModelInterface;
use Qore\SynapseManager\Structure\Entity\SynapseService;
use Qore\SynapseManager\Structure\Entity\SynapseServiceSubject;

interface ModelInterface extends DealingManagerModelInterface
{
    /**
     * @var string
     */
    const CHAIN_PATH = 'handler-path';

    /**
     * @var string
     */
    const CHAIN_CURSOR = 'chain-cursor';

    /**
     * @var string
     */
    const CHAIN_STATE = 'chain-state';

    /**
     * @var string
     */
    const CHAIN_SUBJECT = 'chain-subject';

    /**
     * Set synapse service subject
     *
     * @return bool
     */
    public function isRoot(): bool;

    /**
     * Set synapse service subject
     *
     * @param \Qore\SynapseManager\Structure\Entity\SynapseServiceSubject|null $_subject (optional)
     *
     * @return Model
     */
    public function setSubject(SynapseServiceSubject $_subject = null): Model;

    /**
     * Get synapse service subject
     *
     * @return \Qore\SynapseManager\Structure\Entity\SynapseServiceSubject
     */
    public function getSubject(): ?SynapseServiceSubject;

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
     * @return Model
     */
    public function setServiceCollection(CollectionInterface $_serviceCollection) : Model;

    /**
     * Get last service
     *
     * @return \Qore\SynapseManager\Structure\Entity\SynapseService|null
     */
    public function getLastService() : ?SynapseService;

    /**
     * Get path for current chain point
     *
     * @return string
     */
    public function getPath() : ?string;

    /**
     * Set chain path
     *
     * @param string|null $_path (optional)
     *
     * @return Model
     */
    public function setPath(string $_path = null) : Model;

    /**
     * Get cursor
     *
     * @return ModelInterface
     */
    public function getCursor() : ?ModelInterface;

    /**
     * Set cursor
     *
     * @param ModelInterface|null $_cursor (optional)
     *
     * @return Model
     */
    public function setCursor(ModelInterface $_cursor = null) : Model;
}
