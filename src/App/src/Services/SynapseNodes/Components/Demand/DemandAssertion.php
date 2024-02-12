<?php

namespace Qore\App\SynapseNodes\Components\Demand;

use Laminas\Permissions\Rbac\Rbac;
use Laminas\Permissions\Rbac\RoleInterface;
use Qore\Rbac\AssertionInterface;
use Qore\Rbac\RbacSubjectInterface;

class DemandAssertion implements AssertionInterface
{
    /**
     * @var \Qore\Rbac\RbacSubjectInterface|UserInterface
     */
    protected $_user;

    /**
     * @var mixed
     */
    protected $_demand = null;

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
        $this->_demand = $_object;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function assert(Rbac $_rbac, RoleInterface $_role, string $_permission): bool
    {
        return ! is_null($this->_demand->flows()->firstMatch(['id' => $this->_demand->id]));
    }

}
