<?php

namespace Qore\App\Services\UserStack;

use Closure;
use SplStack;

class UserStack implements UserStackInterface
{
    /**
     * @var SplStack<T>
     */
    private SplStack $stack;

    /**
     * Consctructor
     *
     * @param array $_users 
     */
    public function __construct(array $_users)
    {
        $this->stack = new SplStack();
        foreach ($_users as $user) {
            $this->stack->push($user);
        }
    }

    /**
     * @inheritdoc
     */
    public function __invoke($_user, Closure $_closure)
    {
        return $this->wrap($_user, $_closure);
    }

    /**
     * @inheritdoc
     */
    public function wrap($_user, Closure $_closure)
    {
        $this->stack->push($_user);
        $result = $_closure($_user);
        $this->stack->pop();

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function push($_user): UserStack 
    {
        $this->stack->push($_user);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function current()
    {
        $this->stack->rewind();
        return $this->stack->current();
    }

    /**
     * @inheritdoc
     */
    public function pop()
    {
        return $this->stack->pop();
    }

}
