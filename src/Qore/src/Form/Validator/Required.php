<?php

namespace Qore\Form\Validator;

use Laminas\Validator as LaminasValidator;

class Required extends Validator
{
    /**
     * validatorClass
     *
     * @var mixed
     */
    protected static $validatorClass = LaminasValidator\NotEmpty::class;
}
