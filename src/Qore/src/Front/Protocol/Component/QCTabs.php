<?php

namespace Qore\Front\Protocol\Component;

use Qore\Front\Protocol\ProtocolInterface;
use Qore\Front\Protocol\BaseProtocol;

/**
 * Class: QCTabs
 *
 * @see BaseProtocol
 */
class QCTabs extends BaseProtocol
{
    /**
     * type
     *
     * @var string
     */
    protected $type = 'qc-tabs';

    /**
     * setTabs
     *
     * @param array $_tabs
     */
    public function setTabs(array $_tabs)
    {
        $this->options['tabs'] = [];

        foreach ($_tabs as $key => $tab) {
            $this->setTab($tab);
        }

        return $this;
    }

    /**
     * setTab
     *
     * @param array $_tab
     */
    public function setTab(array $_tab)
    {
        if (! isset($this->options['tabs'])) {
            $this->options['tabs'] = [];
        }

        $key = count($this->options['tabs']) + 1;
        $this->options['tabs'][] = array_merge([
            'label' => $_tab['label'] ?? 'tab-' . $key,
            'components' => [],
        ], $_tab);

        return $this;
    }

    /**
     * prepareTabs
     *
     */
    public function prepareTabs()
    {
        $active = false;
        $components = [];
        foreach ($this->options['tabs'] as &$tab) {
            if (isset($tab['active']) && $tab['active'] == true) {
                if ($active) {
                    unset($tab['active']);
                } else {
                    $active = true;
                }
            }

            foreach ($tab['components'] as &$component) {
                $components[$component->getName()] = $component;
                $component = $component->getName();
            }
        }

        $this->components = $components;

        if ($active === false) {
            reset($this->options['tabs']);
            foreach ($this->options['tabs'] as &$tab) {
                $tab['active'] = true;
                break;
            }
        }

        return $this;
    }

    /**
     * asArray
     *
     */
    public function asArray()
    {
        $this->prepareTabs();
        return parent::asArray();
    }

}
