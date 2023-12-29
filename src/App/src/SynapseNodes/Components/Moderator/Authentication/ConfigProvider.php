<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\Moderator\Authentication;

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
            'invokables' => [
            ],
            'factories' => [
                Adapter\AuthenticationInterface::class => Adapter\AuthenticationFactory::class,
                AuthenticateMiddleware::class => AuthenticateMiddlewareFactory::class,
                ModeratorRepository::class => ModeratorRepositoryFactory::class,
                InitializeUserDataMiddleware::class => InitializeUserDataMiddlewareFactory::class,
            ],
        ];
    }

}
