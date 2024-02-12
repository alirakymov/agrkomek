<?php

declare(strict_types=1);

namespace Qore\App\Services\Tracking;

use Psr\Container\ContainerInterface;
use Qore\Config\ConfigContainer;
use Qore\EventManager\EventManager;

class TrackingFactory
{
    /**
     * @param \Psr\Container\ContainerInterface $_container 
     * @return TrackerInterface 
     */
    public function __invoke(ContainerInterface $_container): TrackingInterface
    {
        $config = $_container->get(ConfigContainer::class);

        $providers = $config('tracking.providers', []);
        foreach ($providers as &$provider) {

            if (! class_exists($provider)) {
                throw new TrackingException(sprintf(
                    'Unknown listener provider class %s.', 
                    $provider
                ));
            }

            if (! in_array(ListenerProviderInterface::class, class_implements($provider))) {
                throw new TrackingException(sprintf(
                    'Listener provider class %s must implement the %s interface.', 
                    $provider, 
                    ListenerProviderInterface::class
                ));
            }

            $provider = $_container->has($provider) 
                ? $_container->get($provider)
                : new $provider();
        }

        return new Tracking(
            $_container->get(EventManager::class), 
            $providers
        );
    }

}
