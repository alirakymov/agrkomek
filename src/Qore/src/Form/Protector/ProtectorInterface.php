<?php

namespace Qore\Form\Protector;

use Psr\Http\Message\ServerRequestInterface;
use Qore\Form\FormManager;

interface ProtectorInterface
{

    /**
     * Set form manager
     *
     * @param \Qore\Form\FormManager $_fm
     *
     * @return ProtectorInterface
     */
    public function setFormManager(FormManager $_fm): ProtectorInterface;

    /**
     * protect - prepare form structure
     *
     * @param \Psr\Http\Message\ServerRequestInterface $_request
     *
     * @return void
     */
    public function protect(ServerRequestInterface $_request) : void;

    /**
     * Inspect form data
     *
     * @param \Psr\Http\Message\ServerRequestInterface $_request
     *
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    public function inspect(ServerRequestInterface $_request) : ServerRequestInterface;

}
