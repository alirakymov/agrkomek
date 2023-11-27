<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\Moderator;

use Qore\Qore;
use Qore\SynapseManager\Structure\Entity\SynapseBaseEntity;

/**
 * Class: Moderator
 *
 * @see Qore\SynapseManager\Structure\Entity\SynapseBaseEntity
 */
class Moderator extends SynapseBaseEntity
{

    /**
     * Reset password
     *
     * @return UserInterface
     */
    public function resetPassword(): Moderator
    {
        $this['password'] = '';
        return $this;
    }

    /**
     * Generate OTP
     *
     * @return UserInterface
     */
    public function generateOtp(): Moderator
    {
        $this['otp'] = rand(100000, 999999);
        return $this;
    }

    /**
     * subscribe
     *
     */
    public static function subscribe()
    {
        parent::subscribe();
        # - Generate password hash and username for user entity
        static::before('save', function($_event) {
            $user = $_event->getTarget();
            # - Generate password hash
            if ($user->password) {
                $passwordDetails = password_get_info($user->password);
                if (is_null($passwordDetails['algo'])) {
                    $user->password = password_hash($user->password, PASSWORD_DEFAULT);
                    $user['otp'] = null;
                }
            } elseif ($user->isNew()) {
                $user->generateOtp();
            }
            # - Genearate username
            if ($user->fullname) {
                $user->fullname = trim(preg_replace('/\s+/', ' ', $user->fullname));
                list($name1, $name2) = explode(' ', $user->fullname, 2);
                $user->username = sprintf(
                    '%s.%s',
                    mb_strtolower(Qore::service(Helper::class)->translit($name2)),
                    mb_strtolower(Qore::service(Helper::class)->translit($name1))
                );
            }
        });

        parent::subscribe();
    }

}
