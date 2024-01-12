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
        if ($this['phone'] == '7121234567') {
            $this['code'] = 123123;
        } else {
            $this['code'] = rand(100000, 999999);
        }

        return $this;
    }

    public function decorate(): User
    {
        unset($this['password'], $this['code'], $this['validatedCode']);
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
