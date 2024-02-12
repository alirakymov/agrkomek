<?php

namespace Qore\App\SynapseNodes\Components\Demand\Agent\InterfaceGateway;

use Qore\InterfaceGateway\Component\AbstractComponent;

/**
 * Demand component - InterfaceGateway class for decorate demand component data
 *
 * @see AbstractComponent 
 */
class DemandComponent extends AbstractComponent
{
    protected $type = 'qc-demand';
}
