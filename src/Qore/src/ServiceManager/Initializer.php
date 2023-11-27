<?php

declare(strict_types=1);

namespace Qore\ServiceManager;

use Qore\Qore;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Initializer\InitializerInterface;

class Initializer implements InitializerInterface
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
        $initializerTargets = Qore::config('dependencies.initializer-targets', []);
        foreach ($initializerTargets as $targetInstance => $initializerActions) {
            if ($_instance instanceof $targetInstance) {
                foreach ($initializerActions as $initializerAction) {
                    if (! is_subclass_of($initializerAction, InitializerInterface::class)) {
                        throw new Exception\BadInitializerAction(
                            vsprintf(
                                'Initializer action %s is not implements of %s',
                                [$initializerAction, InitializerInterface::class]
                            )
                        );
                    }

                    (new $initializerAction())($_container, $_instance);
                }
            }
        }
    }
}
