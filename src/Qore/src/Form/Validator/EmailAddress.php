<?php

namespace Qore\Form\Validator;

use Laminas\Validator as LaminasValidator;

class EmailAddress extends Validator
{
    /**
     * validatorClass
     *
     * @var mixed
     */
    protected static $validatorClass = LaminasValidator\EmailAddress::class;
}
