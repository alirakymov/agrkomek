<?php

namespace Qore\App\SynapseNodes\Components\Operation;

use Qore\App\Services\Tracking\TrackingInterface;
use parallel\Runtime;
use Qore\ORM\Entity\EntityInterface;
use Qore\ORM\Gateway\Gateway;
use Qore\ORM\ModelManager;
use Qore\Qore;
use Qore\SynapseManager\SynapseManager;
use Throwable;

class OperationRuntime
{
    /**
     * @var string - path of boostraping file for parallel
     */
    private string $_bootstrap;

    /**
     * @var Operation
     */
    private Operation $_operation;

    /**
     * @var array
     */
    private array $_options;

    /**
     * Constructor
     *
     * @param string $_bootstrap - path of boostraping file for parallel
     * @param Operation $_operation 
     * @param array $_options (optional) 
     *
     */
    public function __construct(string $_bootstrap, Operation $_operation, array $_options = [])
    {
        $this->_bootstrap = $_bootstrap;
        $this->_operation = $_operation;
        $this->_options = $_options;
    }

    /**
     * Run operation
     *
     * @return void 
     */
    public function run(EntityInterface $_target): void
    {
        # - Refresh target before run operation
        $this->refreshTarget($_target);

        # - Target data for operation launch
        $target = [
            'id' => $_target['id'],
            'entity-name' => $_target->getEntityName(),
        ];

        # - Initialize bootstrap
        $runtime = new Runtime($this->_bootstrap);
        # - Launch operation in thread
        $runtime->run(function($_ioperation, $_target, $_options) {
            try {
                /** @var SynapseManager */
                $sm = Qore::service(SynapseManager::class);
                /** @var ModelManager */
                $mm = Qore::service(ModelManager::class);
                # - Get operation
                $operation = $mm('SM:Operation')
                    ->with('phases')
                    ->where(['@this.id' => $_ioperation])
                    ->one();

                # - Get operation target
                $target = $mm($_target['entity-name'])->where(['@this.id' => $_target['id']])->one();

                # - Launch operation
                $launcher = new OperationLauncher($operation, $target, unserialize($_options));

                /** @var TrackingInterface */
                $tracking = Qore::service(TrackingInterface::class);
                $tracking(function() use ($launcher) {
                    $launcher->launch();
                });

            } catch (Throwable $e) {
                dump($_target);
                dump($e);
            }
        }, [$this->_operation['id'], $target, serialize($this->_options)]);
    }

    /**
     * Refresh target
     *
     * @param \Qore\ORM\Entity\EntityInterface $_target 
     *
     * @return bool
     */
    protected function refreshTarget(EntityInterface $_target): bool
    {
        /** @var SynapseManager */
        $sm = Qore::service(SynapseManager::class);
        /** @var ModelManager */
        $mm = Qore::service(ModelManager::class);
        /** @var Gateway */
        $gw = $mm($_target->getEntityName());

        # - Get operation target
        $run = 0;
        while (is_null($target = $gw->where(['@this.id' => $_target['id']])->one()) && $run++ < 50) {
            sleep(1);
        }

        if (is_null($target)) {
            dump($_target);
        }

        return ! is_null($target);
    }


}
