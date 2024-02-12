<?php

namespace Qore\App\SynapseNodes\Components\Operation;

class ConfigProvider
{
    /**
     * @return array
     */
    public function __invoke() : array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'operation' => [
                'thread' => [
                    'bootstrap' => touch_dir([PROJECT_PATH, 'boot']) . DIRECTORY_SEPARATOR . 'operation.thread.php',
                    'cache' => touch_dir([PROJECT_STORAGE_PATH, 'cache', 'operation']),
                ]
            ]
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
                OperationConstructorInterface::class => OperationConstructorFactory::class,
                OperationPhasesProviderInterface::class => OperationPhasesProviderFactory::class,
            ],
        ];
    }
}
