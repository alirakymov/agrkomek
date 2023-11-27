<?php

declare(strict_types=1);

namespace Qore\ServiceManager\Initializers;


use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Initializer\InitializerInterface;
use Mezzio\Router\RouterInterface;
use Mezzio\Template\TemplateRendererInterface;

class MiddlewaresInitializer implements InitializerInterface
{
    /**
     * __invoke
     *
     * @param ContainerInterface $container
     * @param mixed $instance
     * @return void
     */
    public function __invoke(ContainerInterface $_container, $_instance)
    {
        $_instance->setAuthService($_container->get(\Qore\Auth\AuthenticationService::class));
        $_instance->setTemplateService($_container->get(TemplateRendererInterface::class));
    }

}
