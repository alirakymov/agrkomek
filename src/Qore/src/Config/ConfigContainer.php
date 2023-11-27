<?php

declare(strict_types=1);

namespace Qore\Config;

use ArrayObject;

class ConfigContainer extends ArrayObject implements ConfigContainerInterface
{
    /**
     * Invokable access to FindByPath method
     *
     * @param string $_path
     * @param $_default (optional)
     * @param bool $_wrap (optional)
     *
     * @return mixed
     */
    public function __invoke(string $_path, $_default = null, bool $_wrap = false)
    {
        $config = $this->findByPath($_path) ?? $_default;
        $_wrap && $config = $this->wrap($config);

        return $config;
    }

    /**
     * @inheritdoc
     */
    public function findByPath(string $_path)
    {
        $config = $this;
        $_path = explode('.', $_path);
        foreach ($_path as $point) {
            if (isset($config[$point])) {
                $config = &$config[$point];
            } else {
                return null;
            }
        }

        return $config;
    }

    /**
     * @inheritdoc
     */
    public function wrap(array ...$_merge): ConfigContainerInterface
    {
        return new static(array_merge_recursive(...$_merge));
    }

}
