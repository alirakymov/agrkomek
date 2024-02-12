<?php

namespace Qore\App\SynapseNodes\Components\Demand;

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
                DemandExtenderInterface::class => DemandExtenderFactory::class,
            ],
        ];
    }
}
