<?php

namespace Qore\App\SynapseNodes\Components\User\Authorization;

use Laminas\Permissions\Rbac\Rbac;
use Laminas\Permissions\Rbac\RoleInterface;
use Qore\Rbac\AssertionInterface;
use Qore\Rbac\RbacSubjectInterface;

class LessonAssertion implements AssertionInterface
{
    /**
     * @var \Qore\Rbac\RbacSubjectInterface
     */
    protected RbacSubjectInterface $_user;

    /**
     * @var mixed
     */
    protected $_lesson = null;

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
        $this->_lesson = $_object;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function assert(Rbac $_rbac, RoleInterface $_role, string $_permission): bool
    {
        foreach ($this->_user->flows() as $flow) {
            if ($lesson = $flow->lessons()->firstMatch(['id' => $this->_lesson->id])) {
                return true;
            }
        }

        return false;
    }

}
