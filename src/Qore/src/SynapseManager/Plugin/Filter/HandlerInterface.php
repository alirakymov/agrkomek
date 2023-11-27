<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Plugin\Filter;

interface HandlerInterface
{
    /**
     * Map service data structure to index data structure
     *
     * @param ModelInterface $_model
     * @return bool
     */
    public function build(ModelInterface $_model) : bool;

}
