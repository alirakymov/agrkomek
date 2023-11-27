<?php

namespace Qore\SessionManager;

use Laminas\Session\Container;
use Laminas\Session\SessionManager as ZendSessionManager;

class SessionManager
{
    /**
     * manager
     *
     * @var Laminas\Session\SessionManager|null
     */
    private $manager = null;

    /**
     * sessionContainers
     *
     * @var array
     */
    private $sessionContainers = [];

    /**
     * __construct
     *
     * @param Laminas\Session\SessionManager $_manager
     */
    public function __construct(ZendSessionManager $_manager)
    {
        $this->manager = $_manager;
    }

    /**
     * __invoke
     *
     * @param string $_namespace
     */
    public function __invoke(string $_namespace) : Container
    {
        return $this->getContainer($_namespace);
    }

    /**
     * __get
     *
     * @param string $_property
     */
    public function __get(string $_property)
    {
        return $this->getContainer($_property);
    }

    /**
     * getContainer
     *
     * @param string $_namespace
     */
    public function getContainer(string $_namespace) : SessionContainer
    {
        if (! isset($this->sessionContainers[$_namespace])) {
            $this->sessionContainers[$_namespace] = new SessionContainer($_namespace, $this->manager);
        }

        return $this->sessionContainers[$_namespace];
    }

    /**
     * getManager
     *
     */
    public function getManager() : ZendSessionManager
    {
        return $this->manager;
    }

    /**
     * setManager
     *
     * @param ZendSessionManager $_manager
     */
    public function setManager(ZendSessionManager $_manager) : void
    {
        $this->manager = $_manager;
    }
}
