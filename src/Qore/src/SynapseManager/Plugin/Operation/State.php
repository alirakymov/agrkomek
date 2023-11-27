<?php

namespace Qore\SynapseManager\Plugin\Operation;

use ArrayObject;

class State extends ArrayObject implements StateInterface
{
    /**
     * @inheritdoc
     */
    public function __invoke(string $_index): StateInterface
    {
        return $this->createState($_index);
    }

    /**
     * @inheritdoc
     */
    public function createState(string $_index): StateInterface
    {
        return $this[$_index] ??= new static();
    }

    /**
     * @inheritdoc
     */
    public function merge(array $_array): StateInterface
    {
        $this->exchangeArray(array_merge_recursive($this->getArrayCopy(), $_array));
        return $this;
    }

}
