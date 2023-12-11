<?php

namespace Qore\App\SynapseNodes\Components\User;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use Mezzio\Authentication\OAuth2\Entity\UserEntity;
use Qore\ORM\ModelManager;

class UserRepository implements UserRepositoryInterface
{

    /**
     * @var ModelManager 
     */
    private ModelManager $mm;

    /**
     * Constructor 
     *
     * @param \Qore\ORM\ModelManager $_mm
     */
    public function __construct(ModelManager $_mm)
    {
        $this->mm = $_mm;
    }

    /**
     * @param string $username
     * @param string $password
     * @param string $grantType
     * @return UserEntity|void
     */
    public function getUserEntityByUserCredentials(
        $username,
        $password,
        $grantType,
        ClientEntityInterface $clientEntity
    ) {

        $mm = $this->mm;
        $user = $mm('SM:User')
            ->where(['@this.phone' => $username, '@this.blocked' => 0])
            ->one();

        if (! is_null($user) && $password && (int)$user['code'] !== 0 && $user['code'] === (int)$password) {
            // Обнуляем код регистрации
            $user->code = 0;
            $user->validatedCode = $password;
            $mm($user)->save();
            return new UserEntity($username);
        }
    }

}
