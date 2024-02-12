<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\User;

/**
 * Class: ConfigProvider
 *
 */
class ConfigProvider
{
    /**
     * @return array
     */
    public function __invoke() : array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'synapses' => []
        ];
    }

    /**
     * getDependencies
     *
     */
    private function getDependencies() : array
    {
        return [
            'aliases' => [
                UserStack::class => UserStackInterface::class,
            ],
            'invokables' => [
            ],
            'factories' => [
                UserStackInterface::class => UserStackFactory::class,
            ],
        ];
    }

}
