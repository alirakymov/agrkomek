<?php

namespace Qore\SynapseManager\Plugin\Operation;

interface StateInterface
{
    /**
     * Invokable access to createState method
     *
     * @param string $_index
     *
     * @return StateInterface
     */
    public function __invoke(string $_index) : StateInterface;

    /**
     * Create new state container in current state with _index
     *
     * @param string $_index
     *
     * @return StateInterface
     */
    public function createState(string $_index) : StateInterface;

    /**
     * Merge current array with arrays
     *
     * @param array ...$_array
     *
     * @return StateInterface
     */
    public function merge(array $_array): StateInterface;

}
