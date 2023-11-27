<?php

namespace Qore\Form\Field;

interface FieldInterface
{

    /**
     * Set field data
     *
     * @param mixed $_data
     *
     * @return FieldInterface
     */
    public function setData($_data) : FieldInterface;

    /**
     * Set additional options
     *
     * @param array $array
     *
     * @return FieldInterface
     */
    public function setAdditional(array $array) : FieldInterface;

    /**
     * Set actions for onChange event of this field
     *
     * @param array $_actions
     *
     * @return FieldInterface
     */
    public function setActions(array $_actions) : FieldInterface;

}
