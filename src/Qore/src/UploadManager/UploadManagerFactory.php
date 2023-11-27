<?php

namespace Qore\UploadManager;

use Psr\Container\ContainerInterface;

class UploadManagerFactory
{
    public function __invoke(ContainerInterface $_container)
    {
        $config = $_container->get('config');

        return new UploadManager($config['app']['upload-paths']);
    }
}
