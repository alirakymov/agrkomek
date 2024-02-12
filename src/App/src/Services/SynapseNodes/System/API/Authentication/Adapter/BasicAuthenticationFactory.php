<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\System\API\Authentication\Adapter;

// use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Authentication\Basic\BasicAccess;
use Mezzio\Authentication\Exception\InvalidConfigException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Qore\Qore;
use Qore\App\SynapseNodes\System\API\Authentication\AuthSubjectRepository;
use Qore\App\SynapseNodes\System\API\Authentication\AuthenticationService;
use Webmozart\Assert\Assert;

/**
 * Class: BasicAuthenticationFactory
 *
 */
class BasicAuthenticationFactory
{
    /**
     * __invoke
     *
     * @param ContainerInterface $container
     */
    public function __invoke(ContainerInterface $container): AuthenticationInterface
    {
        $subjectRepository = $container->get(AuthSubjectRepository::class);

        if (null === $subjectRepository) {
            throw new InvalidConfigException(
                'UserRepositoryInterface service is missing for authentication'
            );
        }

        /** @var string|null $realm */
        $realm = Qore::config(sprintf('%s.authentication.realm', AuthenticationService::class)) ?? '';

        /** @var callable|null $responseFactory */
        $responseFactory = $container->get(ResponseInterface::class) ?? null;

        if (null === $responseFactory || ! is_callable($responseFactory)) {
            throw new InvalidConfigException(sprintf(
                'ResponseFactory (%s) value is not present in authentication config or not callable',
                ResponseInterface::class
            ));
        }

        return new BasicAuthentication(
            $subjectRepository,
            $realm,
            $responseFactory
        );
    }

}
