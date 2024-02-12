<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\User\Authentication\Adapter;

use Mezzio\Authentication\UserInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Qore\App\SynapseNodes\Components\User\Authentication\UserRepository;
use Qore\App\SynapseNodes\Components\User\User;
use Qore\SessionManager\SessionManager;

/**
 * Class: AuthenticationAdapter
 *
 * @see AuthenticationInterface
 */
class Authentication implements AuthenticationInterface
{
    /**
     * @var \Qore\App\SynapseNodes\Components\User\Authentication\UserRepository
     */
    protected UserRepository $_userRepository;

    /**
     * @var \Qore\SessionManager\SessionManager
     */
    protected SessionManager $_session;

    /**
     * @var callable|null $_responseFactory
     */
    protected $_responseFactory;

    /**
     * @param \Qore\App\SynapseNodes\Components\User\Authentication\UserRepository $_userRepository
     * @param \Qore\SessionManager\SessionManager $_session
     * @param callable|null $_responseFactory
     */
    public function __construct(UserRepository $_userRepository, SessionManager $_session, $_responseFactory)
    {
        $this->_userRepository = $_userRepository;
        $this->_session = $_session;
        $this->_responseFactory = $_responseFactory;
    }

    /**
     * @inheritdoc
     */
    public function authenticate(ServerRequestInterface $request): ?UserInterface
    {
        $session = ($this->_session)(User::class);
        if (isset($session[static::AUTH_TOKEN])) {
            # - Find user in session
            if (is_null($user = $this->_userRepository->authenticate($session[static::AUTH_TOKEN]))) {
                unset($session[static::AUTH_TOKEN]);
            }
        } elseif (! is_null($request('phone')) && ! is_null($request('password'))) {
            # - Find username & password in request authenticate user and save token to session
            $user = $this->_userRepository->authenticate($request('phone'), $request('password'));
            ! is_null($user) && $session[static::AUTH_TOKEN] = $user->token;
        }

        return $user ?? null;
    }

    /**
     * @inheritdoc
     */
    public function isAuthenticated(): bool
    {
        $session = ($this->_session)(User::class);
        return isset($session[static::AUTH_TOKEN]);
    }

    /**
     * @inheritdoc
     */
    public function signout(): void
    {
        $session = ($this->_session)(User::class);
        unset($session[static::AUTH_TOKEN]);
    }

    /**
     * @inheritdoc
     */
    public function unauthorizedResponse(ServerRequestInterface $request): ResponseInterface
    {
        return ($this->_responseFactory)($request)->withStatus(401);
    }

}
