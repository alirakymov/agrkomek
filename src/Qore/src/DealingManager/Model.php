<?php

declare(strict_types=1);

namespace Qore\DealingManager;

use ArrayObject;

class Model extends ArrayObject implements ModelInterface
{
    use MergeTrait;

    /**
     * __construct
     *
     * @param mixed $_input
     * @param int $_flags
     * @param string $_iteratorClass
     */
    public function __construct($_input = [], int $_flags=0, string $_iteratorClass='ArrayIterator')
    {
        parent::__construct($_input, $_flags, $_iteratorClass);
    }

    /**
     * __get
     *
     * @param string $_property
     */
    public function __get(string $_property)
    {
        return $this[$_property];
    }

    /**
     * __set
     *
     * @param string $_property
     * @param mixed $_value
     */
    public function __set(string $_property, $_value) : void
    {
        $this[$_property] = $_value;
    }

}
