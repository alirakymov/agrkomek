<?php

namespace Qore\Form\Condition;

class Condition
{
    protected $condition = 'custom';
    protected $field;
    protected $data;

    /**
     * __construct
     *
     * @param mixed $_field
     * @param mixed $_data
     * @param mixed $_condition
     */
    public function __construct($_field, $_data, $_condition = null)
    {
        $this->field = $_field;
        $this->data = $_data;
        if (! is_null($_condition)) {
            $this->condition = $_condition;
        }
    }

    /**
     * toArray
     *
     */
    public function decorate() : array
    {
        return [
            'name' => $this->field,
            'condition' => $this->condition,
            'data' => $this->data
        ];
    }
}
