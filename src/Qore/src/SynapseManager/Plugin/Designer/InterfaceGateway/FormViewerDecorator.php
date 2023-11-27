<?php

namespace Qore\SynapseManager\Plugin\Designer\InterfaceGateway;

use Qore\Form\Decorator\QoreFront;
use Qore\Form\FormManager;
use Qore\InterfaceGateway\Component\ComponentInterface;
use Qore\InterfaceGateway\Component\TextBlock;

class FormViewerDecorator extends QoreFront
{
    /**
     * Constructor
     *
     * @param \Qore\Form\FormManager $_form (optional)
     */
    public function getComponent(FormManager $_form = null)
    {
        $_form ??= $this->fm;
        return $this->component ??= ($this->_ig)(WrapperComponent::class, sha1($_form->getName()));
    }

    /**
     * Decorate form manager to viewer component
     *
     * @param \Qore\Form\FormManager $_form
     *
     * @return ComponentInterface
     */
    public function decorate(FormManager $_form)
    {
        return $this->getComponent($_form)->components($this->getFields($_form));
    }

    /**
     * getFields
     *
     * @param Form\FormManager $_form
     */
    protected function getFields(FormManager $_form) : array
    {
        $return = [];

        foreach($_form->getFields() as $field) {

            if (! in_array($field->getType(), ['wysiwyg', 'wysiwyg-inline', 'text'])) {
                continue;
            }

            $return[] = ($this->_ig)(TextBlock::class, sha1($field->getName()))->setText($field->getData());
        }

        return $return;
    }

}
