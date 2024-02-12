<?php

namespace Qore\App\SynapseNodes\Components\Operation;

use Cake\Collection\CollectionInterface;
use Qore\App\SynapseNodes\Components\Demand\Demand;
use Qore\ORM\Entity\EntityInterface;
use Qore\ORM\ModelManager;
use Qore\Qore;
use Qore\QueueManager\JobAbstract;
use Qore\QueueManager\JobInterface;
use Qore\QueueManager\JobTrait;
use Qore\SynapseManager\SynapseManager;
use Throwable;

/**
 * Class: OperationJob
 *
 * @see JobInterface
 * @see JobAbstract
 */
class OperationJob extends JobAbstract implements JobInterface
{
    use JobTrait;

    protected static $name = null;
    protected static $persistence = false;
    protected static $acknowledgement = true;
    protected static $workersNumber = 1;

    /**
     * Process
     *
     * @throws Exception
     *
     * @return bool
     */
    public function process(): bool
    {
        try {

            if (! isset($this->task['event-name'])) {
                throw new Exception('Task must contain "event-name" element');
            }

            $target = $this->task['target'] ?? null;
            if (! $target instanceof EntityInterface) {
                throw new Exception(sprintf('Target object must be instance of %s, but %s given', EntityInterface::class, is_object($target) ? get_class($target) : gettype($target)));
            }

            /** @var ModelManager */
            $mm = Qore::service(ModelManager::class);
            /** @var SynapseManager */
            $sm = Qore::service(SynapseManager::class);

            $connection = $mm->getAdapter()->getDriver()->getConnection();
            $connection->connect();

            /** @var CollectionInterface<Operation> */
            $operations = $mm('SM:Operation')
                ->with('phases')
                ->where(['@this.event' => $this->task['event-name']])
                ->all();

            $options = $this->task;
            unset($options['target']);

            /** @var OperationConstructorInterface */
            $constructor = Qore::service(OperationConstructorInterface::class);
            /** @var CollectionInterface<OperationRuntime> */
            $operationRuntimes = $operations->map(
                fn($_operation) => $constructor->build($_operation, $this->task['target'], $options)
            )->filter(
                fn($_operation) => ! is_null($_operation)
            );

            foreach ($operationRuntimes as $runtime) {
                $runtime->run($target);
            }

            # - Disconnect from database for reconnect 
            $connection->disconnect();

            sleep(1);

            return true;
        } catch (Throwable $e) {
            dump($this->task);
            dump($e);
            return true;
        }
    }

}
