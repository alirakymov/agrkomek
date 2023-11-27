<?php

namespace Qore\Form\Validator;

use Laminas\Validator\Hostname as ValidatorHostname;

class Hostname extends Validator
{
    /**
     * validatorClass
     *
     * @var mixed
     */
    protected static $validatorClass = ValidatorHostname::class;
}
