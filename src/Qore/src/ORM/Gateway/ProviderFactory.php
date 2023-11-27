<?php

declare(strict_types=1);

namespace Qore\ORM\Gateway;

use Psr\Container\ContainerInterface;

class ProviderFactory
{
    public function __invoke(ContainerInterface $_container) : Provider
    {
        return new Provider();
    }
}
