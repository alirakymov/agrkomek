<?php

namespace Qore\SynapseManager\Plugin\Operation;

use Closure;

interface TaskInterface
{
    /**
     * Get task identifier
     *
     * @return string
     */
    public function getIdentifier(): string;

    /**
     * Get operation identifier
     *
     * @return string
     */
    public function getOperationIdentifier(): string;

    /**
     * Get phase identifier
     *
     * @return string
     */
    public function getPhaseIdentifier(): string;

    /**
     * Get phase class
     *
     * @return string
     */
    public function getPhaseClass(): string;

    /**
     * Get class name of storage entity
     *
     * @return string
     */
    public function getEntityClass(): string;

    /**
     * Get task closure
     *
     * @return Closure
     */
    public function getClosure(): Closure;

}
