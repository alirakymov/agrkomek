<?php

namespace Qore\Form\Validator;

use Laminas\Validator as LaminasValidator;

class Regex extends Validator
{
    /**
     * validatorClass
     *
     * @var mixed
     */
    protected static $validatorClass = LaminasValidator\Regex::class;
}
