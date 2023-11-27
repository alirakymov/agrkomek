<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\User\Authentication\Adapter;

use Mezzio\Authentication\AuthenticationInterface as MezzioAuthenticationInterface;

/**
 * Interface: AuthenticationInterface
 *
 * @see MezzioAuthenticationInterface
 */
interface AuthenticationInterface extends MezzioAuthenticationInterface
{
    /**
     * @var stirng - index of user unique auth token in session container
     */
    const AUTH_TOKEN = 'auth-token';

    /**
     * Check user authentication
     *
     * @return bool
     */
    public function isAuthenticated(): bool;

    /**
     * Clear token from user sesssion container
     *
     * @return void
     */
    public function signout(): void;

}
