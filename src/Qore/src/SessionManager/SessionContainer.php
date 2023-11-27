<?php

namespace Qore\SessionManager;

use Laminas\Session\Container;

class SessionContainer extends Container
{
    /**
     * Check for existence
     *
     * @return bool
     */
    public function exists(): bool
    {
        $storage = $this->getStorage();
        return ! is_null($storage[$this->getName()]);
    }

    /**
     * Clear container
     *
     * @return SessionContainer
     */
    public function clear(): SessionContainer
    {
        $storage = $this->getStorage();
        unset($storage[$this->getName()]);
        return $this;
    }

}
