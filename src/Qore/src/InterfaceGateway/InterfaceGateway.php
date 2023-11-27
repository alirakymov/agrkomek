<?php

declare(strict_types=1);

namespace Qore\InterfaceGateway;

use Qore\InterfaceGateway\Component\VoidComponent;

/**
 * Class: InterfaceGateway
 *
 */
class InterfaceGateway
{
    /**
     * _components
     *
     * @var mixed
     */
    private $components = [];

    /**
     * @var array
     */
    private $registered = [];

    /**
     * __construct
     *
     */
    public function __construct()
    {
    }

    /**
     * Generate new component object
     *
     * @param string|array $_class
     * @param string $_name (optional)
     *
     * @return Component\ComponentInterface
     */
    public function __invoke($_class, string $_name = null) : Component\ComponentInterface
    {
        if (is_null($_name)) {

            if (is_array($_class)) {
                return $this->compose($_class);
            }

            $_name = $_class;
            $_class = VoidComponent::class;
        }

        if (! isset($this->components[$_name])) {
            $this->components[$_name] = new $_class($_name);
            $this->components[$_name]->setInterfaceGateway($this);
        }

        return $this->components[$_name];
    }

    /**
     * Register component for group composing
     *
     * @param Component\ComponentInterface $_component
     *
     * @return InterfaceGateway
     */
    public function register(Component\ComponentInterface $_component) : InterfaceGateway
    {
        $this->registered[] = $_component;
        return $this;
    }

    /**
     * Compose to array
     *
     * @param array|null $_components (optional)
     *
     * @return array
     */
    public function compose(array $_components = null) : array
    {
        $result = [];

        $_components ??= $this->registered;

        foreach ($_components as $component) {
            $result = array_merge($result, $component->compose());
        }

        return $result;
    }

}
