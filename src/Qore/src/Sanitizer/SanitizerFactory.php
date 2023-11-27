<?php

declare(strict_types=1);

namespace Qore\Sanitizer;

use HtmlSanitizer\SanitizerBuilder;
use Psr\Container\ContainerInterface;
use Qore\Config\ConfigContainer;

class SanitizerFactory
{
    /**
     * __invoke
     *
     * @param ContainerInterface $container
     */
    public function __invoke(ContainerInterface $container): SanitizerInterface 
    {
        $builder = SanitizerBuilder::createDefault();
        $config = $container->get(ConfigContainer::class)('sanitizer');

        foreach ($config['extensions'] ?? [] as $extension) {
            $builder->registerExtension(new $extension);
        }

        return new Sanitizer($builder, $config['default']);
    }

}
