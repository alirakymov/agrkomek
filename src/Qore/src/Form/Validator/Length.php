<?php

namespace Qore\Form\Validator;

use Laminas\Validator as LaminasValidator;

class Length extends Validator
{
    /**
     * validatorClass
     *
     * @var mixed
     */
    protected static $validatorClass = LaminasValidator\StringLength::class;

    public function isValid($_data)
    {
        if ($_data === '') {
            return true;
        }

        return parent::isValid($_data);
    }
}
