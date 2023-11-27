<?php

namespace Qore\Helper;

use Psr\Container\ContainerInterface;

class HelperFactory
{
    public function __invoke(ContainerInterface $_container)
    {
        return new Helper();
    }
}
