<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\System\API\Authentication;

/**
 * Class: ConfigProvider
 *
 */
class ConfigProvider
{
    /**
     * __invoke
     *
     */
    public function __invoke() : array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'synapses' => [
                AuthenticationService::class => [
                    'realm' => ''
                ]
            ]
        ];
    }

    /**
     * getDependencies
     *
     */
    private function getDependencies() : array
    {
        return [
            'invokables' => [
            ],
            'factories' => [
                Adapter\AuthenticationInterface::class => Adapter\BasicAuthenticationFactory::class,
                AuthenticateMiddleware::class => AuthenticateMiddlewareFactory::class,
                AuthSubjectRepository::class => AuthSubjectRepositoryFactory::class,
            ],
        ];
    }

}
