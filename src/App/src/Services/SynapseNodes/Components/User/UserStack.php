<?php

namespace Qore\App\SynapseNodes\Components\User;

use Closure;
use SplStack;

class UserStack implements UserStackInterface
{
    /**
     * @var SplStack<User>
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
    public function __invoke(User $_user, Closure $_closure)
    {
        return $this->wrap($_user, $_closure);
    }

    /**
     * @inheritdoc
     */
    public function wrap(User $_user, Closure $_closure)
    {
        $this->stack->push($_user);
        $result = $_closure($this->stack->current());
        $this->stack->pop();

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function push(User $_user): UserStack 
    {
        $this->stack->push($_user);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function current(): ?User
    {
        $this->stack->rewind();
        return $this->stack->current();
    }

    /**
     * @inheritdoc
     */
    public function pop(): User
    {
        return $this->stack->pop();
    }

}
