<?php

namespace Qore\Form\Decorator;

use Qore\Form;

interface DecoratorInterface
{
    /**
     * __invoke
     *
     * @param string $_strategy
     */
    public function __invoke(string $_strategy);

    /**
     * setForm
     *
     * @param Form\FormManager $_fm
     */
    public function setForm(Form\FormManager $_fm);

}
