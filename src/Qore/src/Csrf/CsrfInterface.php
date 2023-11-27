<?php

namespace Qore\Csrf;

interface CsrfInterface 
{
    /**
     * Generate token
     *
     * @return string 
     */
    public function generateToken(): string;

    /**
     * Validate token
     *
     * @param string $_token 
     *
     * @return bool 
     */
    public function validateToken(string $_token): bool;

}
