<?php

declare(strict_types=1);

namespace Qore\App\Services\UserStack;

use Psr\Container\ContainerInterface;
use Qore\ORM\ModelManager;
use Qore\SynapseManager\SynapseManager;
use Ramsey\Uuid\Uuid;

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
    private function getSystemUser(ContainerInterface $_container)
    {
        $mm = $_container->get(ModelManager::class);
        $email = 'system@user';

        $user = $mm('QSystem:Users')->where(['@this.email' => $email])->one();

        if (is_null($user)) {
            $user = $mm('QSystem:Users', [
                'email' => $email,
                'firstName' => 'Системный',
                'lastName' => 'Пользователь',
                'password' => Uuid::uuid4(),
            ]);

            $mm($user)->save();
        }

        return $user;
    }

}
