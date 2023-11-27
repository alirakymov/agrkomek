<?php

namespace Qore\SynapseManager\Plugin\Designer\InterfaceGateway;

use Qore\Form\Decorator\QoreFront;
use Qore\Form\FormManager;
use Qore\InterfaceGateway\InterfaceGateway;

class FormDecorator extends QoreFront
{
    /**
     * Constructor
     *
     * @param \Qore\Form\FormManager $_form (optional)
     */
    public function getComponent(FormManager $_form = null)
    {
        $_form ??= $this->fm;
        return $this->component ??= ($this->_ig)(FormComponent::class, $_form->getName());
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
            $return[$field->getName()] = [
                'type' => $field->getType() === 'wysiwyg' ? 'wysiwyg-inline' : $field->getType(),
                'label' => $field->getLabel(),
                'placeholder' => $field->getPlaceholder(),
                'info' => $field->getInfo(),
                'options' => $field->getOptions(),
                'position' => $field->getPosition(),
                'additional' => $field->getAdditional(),
                'conditions' => $field->getConditions(true),
                'actions' => $field->getActions(true),
            ];
        }

        return $return;
    }

}
