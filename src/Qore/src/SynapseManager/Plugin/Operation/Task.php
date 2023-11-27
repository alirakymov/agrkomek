<?php

namespace Qore\SynapseManager\Plugin\Operation;

use Closure;
use Opis\Closure\SerializableClosure;

class Task implements TaskInterface
{
    /**
     * @var Closure|string (serialized)
     */
    protected $_closure;

    /**
     * @var string|null
     */
    protected string $_suffix;

    /**
     * @var string - task identifier
     */
    protected string $_identifier;

    /**
     * @var string
     */
    protected string $_operationIdentifier;

    /**
     * @var string
     */
    private $_entityClass;

    /**
     * @var string
     */
    protected string $_phaseIdentifier;

    /**
     * @var string
     */
    protected string $_phaseClass;

    public function __construct(
        string $_identifier,
        string $_operationIdentifier,
        string $_phaseIdentifier,
        string $_phaseClass,
        string $_entityClass,
        $_closure
    ) {
        $this->_identifier = $_identifier;
        $this->_operationIdentifier = $_operationIdentifier;
        $this->_phaseIdentifier = $_phaseIdentifier;
        $this->_entityClass = $_entityClass;
        $this->_closure = $_closure;
        $this->_phaseClass = $_phaseClass;
    }

    /**
     * @inheritdoc
     */
    public function getClosure(): Closure
    {
        return $this->_closure;
    }

    /**
     * @inheritdoc
     */
    public function getIdentifier(): string
    {
        return $this->_identifier;
    }

    /**
     * @inheritdoc
     */
    public function getOperationIdentifier(): string
    {
        return $this->_operationIdentifier;
    }

    /**
     * @inheritdoc
     */
    public function getPhaseIdentifier(): string
    {
        return $this->_phaseIdentifier;
    }

    /**
     * @inheritdoc
     */
    public function getPhaseClass(): string
    {
        return $this->_phaseClass;
    }

    /**
     * @inheritdoc
     */
    public function getEntityClass(): string
    {
        return $this->_entityClass;
    }

    /**
     * Serialize current task object
     *
     * @return array
     */
    public function __serialize(): array
    {
        return [
            '_identifier' => $this->_identifier,
            '_operationIdentifier' => $this->_operationIdentifier,
            '_phaseIdentifier' => $this->_phaseIdentifier,
            '_phaseClass' => $this->_phaseClass,
            '_entityClass' => $this->_entityClass,
            '_closure' => ! is_string($this->_closure)
                ? serialize(new SerializableClosure($this->_closure))
                : $this->_closure,
        ];
    }

    /**
     * Unserialize task object
     *
     * @param array $_data
     *
     * @return void
     */
    public function __unserialize(array $_data): void
    {
        $this->_identifier = $_data['_identifier'];
        $this->_operationIdentifier = $_data['_operationIdentifier'];
        $this->_phaseIdentifier = $_data['_phaseIdentifier'];
        $this->_phaseClass = $_data['_phaseClass'];
        $this->_entityClass = $_data['_entityClass'];
        $this->_closure = unserialize($_data['_closure'])->getClosure();
    }

}
