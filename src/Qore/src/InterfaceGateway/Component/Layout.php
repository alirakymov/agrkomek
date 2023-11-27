<?php

namespace Qore\InterfaceGateway\Component;

class Layout extends AbstractComponent
{
    /**
     * type
     *
     * @var string
     */
    protected $type = 'ql-main';

    /**
     * @var ?string
     */
    protected ?string $parent = 'qore-app';

    /**
     * navbar
     *
     * @param array $_navigation
     */
    public function navbar(array $_navbar)
    {
        $this->setLayoutComponent('navbar', $_navbar);
        return $this;
    }

    /**
     * Layout
     *
     * @param array $_navpills 
     *
     * @return Layout
     */
    public function navpills(array $_navpills): Layout
    {
        $this->setLayoutComponent('navpills', $_navpills);
        return $this;
    }

    /**
     * navbuttons
     *
     * @param array $_navpills
     */
    public function navpanel(array $_buttons)
    {
        $this->setLayoutComponent('navpanel', $_buttons);
        return $this;
    }

    /**
     * Set navigation panel buttons
     *
     * @param array $_buttons 
     *
     * @return Layout 
     */
    public function navbuttons(array $_buttons): Layout
    {
        $this->setLayoutComponent('navbuttons', $_buttons);
        return $this;
    }

    /**
     * Set stomp options
     *
     * @param array $_stomp
     *
     * @return Layout
     */
    public function stomp(array $_stomp): Layout
    {
        $this->setOption('stomp',$_stomp);
        return $this;
    }

    /**
     * Register logout button
     *
     * @param array $_logout
     *
     * @return ComponentInterface
     */
    public function logout(array $_logout): ComponentInterface
    {
        $this->setOption('logout', $_logout);
        return $this;
    }

    /**
     * layoutComponents
     *
     * @param string $_componentName
     * @param array $_component
     */
    private function setLayoutComponent(string $_componentName, array $_component)
    {
        if (!isset($this->options['layout-components'])) {
            $this->options['layout-components'] = [];
        }

        $this->options['layout-components'][$_componentName] = $_component;
    }

}
