<?php

namespace Qore\Form\Decorator;

use Qore\Form;
use Qore\Front as QFront;
use Qore\InterfaceGateway\Component\Form as QoreForm;
use Qore\InterfaceGateway\InterfaceGateway;

class QoreFront implements DecoratorInterface
{
    /**
     * fm
     *
     * @var mixed
     */
    protected $fm = null;

    /**
     * component
     *
     * @var mixed
     */
    protected $component = null;

    /**
     * @var Form\Decorator\InterfaceGateway
     */
    protected $_ig;

    /**
     * Constructor
     *
     * @param InterfaceGateway $_ig
     */
    public function __construct(InterfaceGateway $_ig)
    {
        $this->_ig = $_ig;
    }

    /**
     * __invoke
     *
     * @param string $_strategy
     */
    public function __invoke($_strategies)
    {
        if (! is_array($_strategies)) {
            $_strategies = [$_strategies];
        }

        foreach ($_strategies as $strategy) {
            $method = array_map(function($_part){
                return ucfirst($_part);
            }, explode('-', $strategy));
            call_user_func([$this, 'strategy' . implode('', $method)]);
        }
        return $this->component;
    }

    /**
     * strategySetFields
     *
     */
    public function strategySetFields()
    {
        return $this->getComponent()->run('setFields', [
            'fields' => $this->getFields($this->fm)
        ]);
    }

    /**
     * strategyReloadFields
     *
     */
    public function strategyReloadFields()
    {
        return $this->getComponent()->run('reloadFields', [
            'fields' => $this->getFields($this->fm)
        ]);
    }

    /**
     * strategyUpdateModel
     *
     */
    public function strategyUpdateModel()
    {
        return $this->getComponent()->run('updateModel', [
            'model' => $this->fm->getData(false)
        ]);
    }

    /**
     * strategyDecorate
     *
     */
    public function strategyDecorate()
    {
        return $this->decorate($this->fm);
    }

    /**
     * setForm
     *
     * @param Form\FormManager $_fm
     */
    public function setForm(Form\FormManager $_fm)
    {
        $this->component = $this->getComponent($this->fm = $_fm);
    }

    /**
     * getComponent
     *
     * @param Form\FormManager $_form
     */
    public function getComponent(Form\FormManager $_form = null)
    {
        $_form ??= $this->fm;
        return $this->component ??= ($this->_ig)(QoreForm::class, $_form->getName());
    }

    /**
     * decorate
     *
     * @param Form\FormManager $_form
     */
    public function decorate(Form\FormManager $_form)
    {
        return $this->getComponent($_form)
            ->setAction($_form->getAction())
            ->setMethod($_form->getMethod())
            ->setFields($this->getFields($_form))
            ->setModel($_form->getData(false))
            ->setErrors($_form->getErrors());
    }

    /**
     * decorateFields
     *
     * @param Form\FormManager $_form
     */
    public function decorateFields(Form\FormManager $_form)
    {
        return $this->getComponent($_form)
            ->run('reloadFields', [
                'fields' => $this->getFields($_form),
            ]);
    }

    /**
     * getFields
     *
     * @param Form\FormManager $_form
     */
    protected function getFields(Form\FormManager $_form) : array
    {
        $return = [];

        foreach($_form->getFields() as $field) {
            $return[$field->getName()] = [
                'type' => $field->getType(),
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
