<?php

namespace Qore\Front\Protocol\Component;

use Qore\Front\Protocol\ProtocolInterface;
use Qore\Front\Protocol\BaseProtocol;

/**
 * Class: QCTabs
 *
 * @see BaseProtocol
 */
class QCTimeline extends BaseProtocol
{
    /**
     * type
     *
     * @var string
     */
    protected $type = 'qc-timeline';

    /**
     * asArray
     *
     */
    public function asArray()
    {
        if (isset($this->options['events'])) {
            foreach ($this->options['events'] as &$event) {
                $event['components'] ??= [];
                foreach ($event['components'] as &$component) {
                    $component = is_object($component) && $component instanceof ProtocolInterface
                        ? $component->asArray()
                        : (array)$component;
                }
            }
        }

        return parent::asArray();
    }

}
