<?php

namespace Qore\App\SynapseNodes\Components\User;

use Closure;

interface UserStackInterface
{

    /**
     * Easy access to wrap user method
     *
     * @param User $_user 
     * @param \Closure $_closure 
     *
     * @return mixed 
     */
    public function __invoke(User $_user, Closure $_closure);

    /**
     * Wrap user
     *
     * @param User $_user 
     * @param \Closure $_closure 
     *
     * @return mixed 
     */
    public function wrap(User $_user, Closure $_closure);

    /**
     * Push user
     *
     * @param User $_user 
     *
     * @return
     */
    public function push(User $_user): UserStackInterface;

    /**
     * Get current user
     *
     * @return User 
     */
    public function current(): ?User;

    /**
     * Pop user
     *
     * @return User
     */
    public function pop(): User;

}
