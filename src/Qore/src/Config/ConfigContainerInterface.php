<?php

namespace Qore\Config;

interface ConfigContainerInterface
{
    /**
     * Wrap configuration arrays of options to container interface
     *
     * @param array $_merge
     *
     * @return ConfigContainerInterface
     */
    public function wrap(array ...$_merge): ConfigContainerInterface;

    /**
     * Find configuration option by path exploded by dot
     * For example: `app.config.debug`
     *
     * @param string $_path
     *
     * @return mixed
     */
    public function findByPath(string $_path);

}
