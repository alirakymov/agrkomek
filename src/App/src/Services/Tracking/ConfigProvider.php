<?php

namespace Qore\App\Services\Tracking;

class ConfigProvider
{
    /**
     * @return array
     */
    public function __invoke() : array
    {
        return [
            'dependencies' => $this->getDependencies(),
        ];
    }

    /**
     * Return dependencies of this synapse 
     *
     * @return array 
     */
    private function getDependencies() : array
    {
        return [
            'invokables' => [
            ],
            'factories' => [
                TrackingInterface::class => TrackingFactory::class,
            ],
        ];
    }

}
