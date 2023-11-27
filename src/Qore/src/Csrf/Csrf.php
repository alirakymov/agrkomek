<?php

declare(strict_types=1);

namespace Qore\Csrf;

use Qore\Csrf\Storage\StorageInterface;

class Csrf implements CsrfInterface 
{
    /**
     * @var StorageInterface
     */
    private $_storage;

    /**
     * Constructor
     *
     * @param \Qore\Csrf\Storage\StorageInterface $_storage 
     */
    public function __construct(StorageInterface $_storage)
    {
        $this->_storage = $_storage;
    }

    /**
     * @inheritdoc
     */
    public function generateToken(): string
    {
        $this->_storage->push($token = bin2hex(random_bytes(16)));
        return $token;
    }

    /**
     * @inheritdoc
     */
    public function validateToken(string $_token): bool
    {
        return $this->_storage->pull($_token);
    }

}
