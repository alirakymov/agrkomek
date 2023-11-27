<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Plugin\Report;

interface HandlerInterface
{
    /**
     * Combine mapping from data of model
     *
     * @param ModelInterface $_model
     *
     * @return bool
     */
    public function map(ModelInterface $_model) : bool;
}
