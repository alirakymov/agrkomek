<?php

namespace Qore\ImageManager;

use Psr\Container\ContainerInterface;

class ImageManagerFactory
{
    public function __invoke(ContainerInterface $_container)
    {
        return new ImageManager();
    }
}
