<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\User;

use Psr\Container\ContainerInterface;
use Qore\SynapseManager\SynapseManager;

class UserStackFactory
{
    /**
     * __invoke
     *
     * @param ContainerInterface $container
     */
    public function __invoke(ContainerInterface $container): UserStackInterface 
    {
        return new UserStack([$this->getSystemUser($container)]);
    }

    /**
     * Get system user instance
     *
     * @param \Psr\Container\ContainerInterface $container 
     *
     * @return User 
     */
    private function getSystemUser(ContainerInterface $_container): User
    {
        $sm = $_container->get(SynapseManager::class);

        $userName = 'system.user';

        $user = $sm('User:Manager')->mm()->where(['@this.username' => $userName])->one();

        if (is_null($user)) {
            $user = $sm('User:Manager')->mm([
                'username' => $userName,
                'fullname' => 'User System',
            ]);
        }

        return $user;
    }

}
