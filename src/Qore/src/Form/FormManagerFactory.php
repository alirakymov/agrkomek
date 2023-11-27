<?php

namespace Qore\Form;

use Psr\Container\ContainerInterface;
use Qore\Form\Decorator\QoreFront;
use Qore\SessionManager\SessionManager;

class FormManagerFactory
{
    /**
     * __invoke
     *
     * @param ContainerInterface $_container
     */
    public function __invoke(ContainerInterface $_container)
    {
        $config = $_container->get('config')['app']['form-manager'] ?? null;

        if (is_null($config)) {
            throw new FormManagerException(sprintf("Please setup FormManager library for your application!"));
        }

        if (! isset($config['token-key'], $config['token-salt'])) {
            throw new FormManagerException(sprintf("Please set token-key and token-salt params to config your application"));
        }

        return new FormManager(
            $_container->get(QoreFront::class),
            $_container->get(SessionManager::class),
            $config
        );
    }
}
