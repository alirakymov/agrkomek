<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\User;

use Qore\Qore;
use Qore\SynapseManager\Structure\Entity\SynapseBaseEntity;

/**
 * Class: Article
 *
 * @see Qore\SynapseManager\Structure\Entity\SynapseBaseEntity
 */
class User extends SynapseBaseEntity
{
    /**
     * Generate OTP
     *
     * @return UserInterface
     */
    public function generateOtp(): User
    {
        $this['code'] = rand(100000, 999999);
        return $this;
    }

    /**
     * subscribe
     *
     */
    public static function subscribe()
    {
        parent::subscribe();
    }

}
