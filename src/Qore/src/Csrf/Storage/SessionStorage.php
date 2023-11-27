<?php

namespace Qore\Csrf\Storage;

use Laminas\Session\Container;
use Qore\SessionManager\SessionManager;

class SessionStorage implements StorageInterface
{
    const CONTAINER_INDEX = '__csrf';

    /**
     * @var Container
     */
    private Container $_container;

    /**
     * @var int 
     */
    private int $size;
    
    /**
     * Constructor
     *
     * @param \Qore\SessionManager\SessionManager $_sessionManager
     */
    public function __construct(SessionManager $_sessionManager, int $_size = 50)
    {
        $this->_container = $_sessionManager(StorageInterface::class);
        $this->size = $_size;
    }

    /**
     * @inheritdoc
     */
    public function pull(string $_token): bool
    {
        $container = $this->getContainer();

        if (($index = array_search($_token, $container)) !== false) {
            unset($container[$index]);
            $this->setContainer($container);
            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function push(string $_token): void
    {
        $container = $this->getContainer();
        if (($count = count($container)) >= $this->size) {
            $container = array_slice($container, $count - $this->size + 1);
        }

        $container[] = $_token;
        $this->setContainer($container);
    }

    /**
     * Return tokens container
     *
     * @return array
     */
    private function getContainer(): array
    {
        return $this->_container[static::CONTAINER_INDEX] ?? [];
    }

    /**
     * Set tokens container to session container
     *
     * @param array $_container 
     *
     * @return void
     */
    private function setContainer(array $_container): void
    {
        $this->_container[static::CONTAINER_INDEX] = $_container;
    }

}
