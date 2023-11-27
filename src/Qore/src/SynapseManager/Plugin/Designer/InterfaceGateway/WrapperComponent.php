<?php

namespace Qore\SynapseManager\Plugin\Designer\InterfaceGateway;

use Qore\InterfaceGateway\Component\AbstractComponent;

class WrapperComponent extends AbstractComponent implements WrapperInterface
{
    protected $type = 'qd-wrapper';
}
