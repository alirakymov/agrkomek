<?php

namespace Qore\App\SynapseNodes\Components\User;

use Mezzio\Authentication\UserInterface as AuthenticationUserInterface;

interface UserInterface extends AuthenticationUserInterface
{
    /**
     * Retrive lastname from fullname field
     *
     * @return string
     */
    public function getLastname(): string;

    /**
     * Retrive firstname from fullname field
     *
     * @return string
     */
    public function getFirstname(): string;

    /**
     * Retrive meet member identity
     *
     * @return string
     */
    public function getMeetMemberIdentity(): string;

    /**
     * Split fullname
     * - Doe John -> [
     *      'firstname' => 'John',
     *      'lastname' => 'Doe',
     *   ]
     *
     * @return array
     */
    public function splitFullname(): array;

    /**
     * Generate token for authentication
     *
     * @return string
     */
    public function generateToken(): string;

}
