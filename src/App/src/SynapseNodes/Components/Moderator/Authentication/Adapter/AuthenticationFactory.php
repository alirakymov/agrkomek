<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\User\Authentication\Adapter;

use Mezzio\Authentication\Exception\InvalidConfigException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Qore\App\SynapseNodes\Components\User\Authentication\UserRepository;
use Qore\SessionManager\SessionManager;

/**
 * Class: BasicAuthenticationFactory
 *
 */
class AuthenticationFactory
{
    /**
     * __invoke
     *
     * @param ContainerInterface $container
     */
    public function __invoke(ContainerInterface $container): AuthenticationInterface
    {
        $userRepository = $container->get(UserRepository::class);

        if (null === $userRepository) {
            throw new InvalidConfigException(
                'UserRepositoryInterface service is missing for authentication'
            );
        }

        /** @var callable|null $responseFactory */
        $responseFactory = $container->get(ResponseInterface::class) ?? null;

        if (null === $responseFactory || ! is_callable($responseFactory)) {
            throw new InvalidConfigException(sprintf(
                'ResponseFactory (%s) value is not present in authentication config or not callable',
                ResponseInterface::class
            ));
        }

        return new Authentication(
            $userRepository,
            $container->get(SessionManager::class),
            $responseFactory
        );
    }

}
