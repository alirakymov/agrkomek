<?php

namespace Qore\Core\Entities;

use Qore\NotifyManager\SubscriberInterface;

/**
 * Class: QSystemUser
 *
 * @see QSystemBase
 */
class QSystemUser extends QSystemBase
{
    /**
     * subscribe
     *
     */
    public static function subscribe(): void
    {
        parent::subscribe();

        static::before('save', function($_event) {
            $user = $_event->getTarget();
            $passwordDetails = password_get_info($user->password);
            if (is_null($passwordDetails['algo'])) {
                $user->password = password_hash($user->password, PASSWORD_DEFAULT);
            }
        });
    }

    /**
     * @return string
     */
    public function getUniqueHash() : string
    {
        return sha1($this->id);
    }
}
