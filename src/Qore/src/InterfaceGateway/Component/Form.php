<?php

declare(strict_types=1);

namespace Qore\InterfaceGateway\Component;

class Form extends AbstractComponent
{
    /**
     * type
     *
     * @var string
     */
    protected $type = 'qc-form';

    /**
     * setFields
     *
     * @param array $_fields
     */
    public function setFields(array $_fields) : Form
    {
        $this->options['fields'] = $_fields;
        return $this;
    }

    /**
     * setAction
     *
     * @param string $_action
     */
    public function setAction(string $_action) : Form
    {
        $this->options['action'] = $_action;
        return $this;
    }

    /**
     * setMethod
     *
     * @param string $_method
     */
    public function setMethod(string $_method) : Form
    {
        $this->options['method'] = $_method;
        return $this;
    }

    /**
     * setMessage
     *
     * @param string $_message
     */
    public function setMessage(array $_message) : Form
    {
        $this->options['messages'] ??= [];
        $this->options['messages'][] = $_message;
        return $this;
    }

    /**
     * setErrors
     *
     * @param array $_errors
     */
    public function setErrors(array $_errors) : Form
    {
        $this->options['errors'] = $_errors;
        return $this;
    }

    /**
     * setModel
     *
     * @param array $_model
     */
    public function setModel(array $_model) : Form
    {
        $this->options['model'] = $_model;
        return $this;
    }

}
