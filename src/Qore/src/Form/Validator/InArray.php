<?php

namespace Qore\Form\Validator;

use Laminas\Validator as LaminasValidator;

class InArray extends Validator
{
    /**
     * validatorClass
     *
     * @var mixed
     */
    protected static $validatorClass = LaminasValidator\InArray::class;
}
