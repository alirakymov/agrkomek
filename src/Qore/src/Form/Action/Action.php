<?php

namespace Qore\Form\Action;

class Action
{
    protected $action = 'custom';
    protected $data;

    /**
     * __construct
     *
     * @param mixed $_data
     * @param mixed $_action
     */
    public function __construct($_data, $_action = null)
    {
        $this->data = $_data;
        if (! is_null($_action)) {
            $this->action = $_action;
        }
    }

    /**
     * toArray
     *
     */
    public function decorate() : array
    {
        return [
            'data' => $this->data,
            'name' => $this->action,
        ];
    }

}
