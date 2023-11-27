<?php

namespace Qore\InterfaceGateway\Component\Tabs;

use Qore\InterfaceGateway\Component\AbstractComponent;

class Tab extends AbstractComponent
{
    /**
     * type
     *
     * @var string
     */
    protected $type = 'qct-tab';

    /**
     * Set label to navigation link of this tab
     *
     * @param string $_label
     *
     * @return Tab
     */
    public function setLabel(string $_label) : Tab
    {
        $this->options['label'] = $_label;
        return $this;
    }

}
