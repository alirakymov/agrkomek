<?php

namespace Qore\App\Services\UserStack;

use Closure;

interface UserStackInterface
{

    /**
     * Easy access to wrap user method
     *
     * @param mixed $_user 
     * @param \Closure $_closure 
     *
     * @return mixed 
     */
    public function __invoke($_user, Closure $_closure);

    /**
     * Wrap user
     *
     * @param mixed $_user 
     * @param \Closure $_closure 
     *
     * @return mixed 
     */
    public function wrap($_user, Closure $_closure);

    /**
     * Push user
     *
     * @param mixed $_user 
     *
     * @return UserStackInterface
     */
    public function push($_user): UserStackInterface;

    /**
     * Get current user
     *
     * @return mixed 
     */
    public function current();

    /**
     * Pop user
     *
     * @return mixed 
     */
    public function pop();

}
