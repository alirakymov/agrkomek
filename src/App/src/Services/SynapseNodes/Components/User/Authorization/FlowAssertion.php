<?php

namespace Qore\App\SynapseNodes\Components\User\Authorization;

use Laminas\Permissions\Rbac\Rbac;
use Laminas\Permissions\Rbac\RoleInterface;
use Qore\App\SynapseNodes\Components\User\UserInterface;
use Qore\Rbac\AssertionInterface;
use Qore\Rbac\RbacSubjectInterface;

class FlowAssertion implements AssertionInterface
{
    /**
     * @var \Qore\Rbac\RbacSubjectInterface|UserInterface
     */
    protected $_user;

    /**
     * @var mixed
     */
    protected $_flow = null;

    /**
     * @inheritdoc
     */
    public function setSubject(RbacSubjectInterface $_subject): AssertionInterface
    {
        $this->_user = $_subject;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setObject($_object): AssertionInterface
    {
        $this->_flow = $_object;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function assert(Rbac $_rbac, RoleInterface $_role, string $_permission): bool
    {
        return ! is_null($this->_user->flows()->firstMatch(['id' => $this->_flow->id]));
    }

}
