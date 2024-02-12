<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\User\Authentication;

use Mezzio\Authentication\UserInterface;
use Mezzio\Authentication\UserRepositoryInterface;
use Qore\ORM\ModelManager;
use Qore\Qore;

class UserRepository implements UserRepositoryInterface
{
    /**
     * @var \Qant\ModelManager\ModelManager
     */
    protected ModelManager $_mm;

    /**
     * @param \Qore\ORM\ModelManager $_mm
     */
    public function __construct(ModelManager $_mm)
    {
        $this->_mm = $_mm;
    }

    /**
     * @param string $_credential
     * @param string|null $_password (optional)
     *
     * @return ?UserInterface
     */
    public function authenticate(string $_credential, ?string $_password = null): ?UserInterface
    {
        if (is_null($_password)) {
            # Find user by token
            $user = ($this->_mm)('SM:User')->where(function($_where) use ($_credential) {
                $_where(['@this.token' => $_credential]);
            })->one();
        } else {
            # Find user by phone & password
            $user = ($this->_mm)('SM:User')->where(function($_where) use ($_credential) {
                $_where(['@this.phone' => $this->preparePhone($_credential)]);
            })->one();

            $user = ! is_null($user) && $user->password && password_verify($_password, $user->password) ? $user : null;

            if (! is_null($user)) {
                $user->generateToken();
                ($this->_mm)($user)->save();
            }
        }

        return $user;
    }

    /**
     * Prepare phone
     *
     * @param  $_value
     *
     * @return string
     */
    protected function preparePhone($_value): string
    {
        $_value = preg_replace('/[^0-9]/', '', $_value);
        return mb_strlen($_value) > 10 ? mb_substr($_value, -10) : $_value;
    }

}
