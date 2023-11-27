<?php

namespace Qore\InterfaceGateway\Component\Tabs;

use Qore\InterfaceGateway\Component\AbstractComponent;

class Tabs extends AbstractComponent
{
    /**
     * type
     *
     * @var string
     */
    protected $type = 'qc-tabs-improve';

    /**
     * @var array<Tab>
     */
    protected $tabs = [];

    /**
     * Generate or get exists tab component instance
     *
     * @param string $_name
     *
     * @return Tab
     */
    public function createTab(string $_name) : Tab
    {
        return $this->tabs[$_name] ??= ($this->ig)(
            Tab::class,
            $this->getTabComponentName($_name)
        );
    }

    /**
     * Alias for createTab
     *
     * @param string $_name
     *
     * @return Tab
     */
    public function getTab(string $_name) : Tab
    {
        return $this->createTab($_name);
    }

    /**
     * Create or get tab and run closure
     *
     * @param string $_name
     * @param \Closure $_closure (optional)
     *
     * @return Tabs|Tab
     */
    public function tab(string $_name, \Closure $_closure = null)
    {
        $tab = $this->createTab($_name);
        # - return tab if closure is not setted
        if (is_null($_closure)) {
            return $tab;
        }

        $_closure($tab);

        return $this;
    }

    /**
     * Generate unique tab component name
     *
     * @param string $_tabName
     *
     * @return string
     */
    public function getTabComponentName(string $_tabName) : string
    {
        return sprintf('%s.%s', $this->name, $_tabName);
    }

    /**
     * Set indents to navigation bar
     *
     * @param bool $_indents
     *
     * @return Tabs
     */
    public function indents(bool $_indents) : Tabs
    {
        $this->options['indents'] = $_indents;
        return $this;
    }

    /**
     * Compose tabs to array
     *
     * @return array
     */
    public function compose(): array
    {
        $this->components = array_values($this->tabs);
        return parent::compose();
    }


}
