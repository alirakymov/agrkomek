<?php

namespace Qore\Front\Protocol\Layout;

use Qore\Front\Protocol\BaseProtocol;

class QLMain extends BaseProtocol
{
    /**
     * type
     *
     * @var string
     */
    protected $type = 'ql-main';

    /**
     * navbar
     *
     * @var array
     */
    protected $navbar = [];

    /**
     * navbar
     *
     * @param array $_navigation
     */
    public function navbar(array $_navbar)
    {
        $this->layoutComponents('navbar', $_navbar);
        return $this;
    }

    /**
     * navpills
     *
     */
    public function navpills(array $_navpills)
    {
        $this->layoutComponents('navpills', $_navpills);
        return $this;
    }

    /**
     * navbuttons
     *
     * @param array $_navpills
     */
    public function navbuttons(array $_buttons)
    {
        $this->layoutComponents('navpanel', $_buttons);
        return $this;
    }

    /**
     * layoutComponents
     *
     * @param string $_componentName
     * @param array $_component
     */
    private function layoutComponents(string $_componentName, array $_component)
    {
        if (!isset($this->options['layoutComponents'])) {
            $this->options['layoutComponents'] = [];
        }

        $this->options['layoutComponents'][$_componentName] = $_component;
    }

}
