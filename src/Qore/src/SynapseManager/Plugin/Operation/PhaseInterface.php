<?php

namespace Qore\SynapseManager\Plugin\Operation;

use Qore\DealingManager\ResultInterface;

interface PhaseInterface extends HandlerInterface
{

    /**
     * Initialize action for current phase in chain sequence
     *
     * @param ModelInterface $_model
     *
     * @return void
     */
    public function initialize(ModelInterface $_model) : void;

    /**
     * Process action for current phase in chain sequence
     *
     * @param ModelInterface $_model
     *
     * @return \Qore\DealingManager\ResultInterface
     */
    public function process(ModelInterface $_model) : ResultInterface;

}
