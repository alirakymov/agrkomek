<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Plugin\Report;

interface ExecutableInterface
{
    public function execute(ModelInterface $_model) : bool;
}
