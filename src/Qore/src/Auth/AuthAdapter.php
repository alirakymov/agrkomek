<?php

declare(strict_types=1);

namespace Qore\Auth;


use Qore\Qore;
use Laminas\Authentication\Adapter\AdapterInterface;
use Laminas\Authentication\Result;

class AuthAdapter implements AdapterInterface
{
    private $password;
    private $useremail;

    /**
     * setAuthData
     *
     * @param string $_useremail
     * @param string $_password
     */
    public function setAuthData(string $_useremail, string $_password)
    {
        $this->useremail = $_useremail;
        $this->password = $_password;

        return $this;
    }

    /**
     * authenticate
     *
     */
    public function authenticate()
    {
        $mm = Qore::service(\Qore\ORM\ModelManager::class);
        $result = $mm('QSystem:Users')
            ->where(function($_where){
                $_where(['@this.email' => $this->useremail]);
            })->all();

        $result = $result->first();

        if (! is_null($result) && password_verify($this->password, $result->password)) {
            return new Result(Result::SUCCESS, $result);
        }

        return new Result(Result::FAILURE_CREDENTIAL_INVALID, $this->useremail);
    }

}
