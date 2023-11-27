<?php

namespace Qore\Csrf\Storage;

interface StorageInterface
{
    /**
     * Pull token from storage
     *
     * @param string $_token 
     *
     * @return string 
     */
    public function pull(string $_token): bool;

    /**
     * Push token to storage
     *
     * @param string $_token
     *
     * @return void 
     */
    public function push(string $_token): void;

}
