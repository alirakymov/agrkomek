<?php

namespace Qore\Form\Validator;

use Laminas\Validator as LaminasValidator;

/**
 * Class: Callback
 *
 * @see Validator
 */
class Callback extends Validator
{
    /**
     * validatorClass
     *
     * @var mixed
     */
    protected static $validatorClass = LaminasValidator\Callback::class;
}
