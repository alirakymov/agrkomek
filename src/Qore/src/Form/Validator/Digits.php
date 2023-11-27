<?php

namespace Qore\Form\Validator;

use Laminas\Validator\Digits as ValidatorDigits;

class Digits extends Validator
{
    /**
     * validatorClass
     *
     * @var mixed
     */
    protected static $validatorClass = ValidatorDigits::class;
}
