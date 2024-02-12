<?php

namespace Qore\App\SynapseNodes\Components\User;

use Qore\Form\FormManager;

interface PhoneVerifiableInterface
{
    /**
     * Get form component
     *
     * @return FormManager
     */
    public function getForm(): FormManager;

}
