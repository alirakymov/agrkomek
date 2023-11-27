<?php

namespace Qore\SynapseManager\Plugin\Operation;

use Qore\Qore;
use Qore\QueueManager\JobAbstract;
use Qore\QueueManager\JobInterface;
use Qore\QueueManager\JobTrait;
use Qore\QueueManager\QueueManager;
use Symfony\Component\Process\Process;

/**
 * Class: TaskExecutor
 *
 * @see JobInterface
 * @see JobAbstract
 */
class TaskQueue extends JobAbstract implements JobInterface
{
    use JobTrait;

    protected static $name = null;
    protected static $persistence = false;
    protected static $acknowledgement = true;
    protected static $workersNumber = 1;

    /**
     * @var array<>
     */
    protected static array $processes = [];

    /**
     * process
     *
     */
    public function process() : bool
    {
        if (! isset($this->task['identifier'])) {
            return true;
        }

        $identifiers = ! is_array($this->task['identifier'])
            ? [$this->task['identifier']]
            : $this->task['identifier'];

        try {
            # - Run tasks
            foreach ($identifiers as $identifier) {
                # - Skip launching if task process already running
                if (isset(static::$processes[$identifier])) {
                    if (static::$processes[$identifier]->isRunning()) {
                        continue;
                    }
                }

                # - Get task process and save it to static property
                static::$processes[$identifier] = $this->getProcess($identifier);
                static::$processes[$identifier]->start();
            }

            # - Clear cache container
            foreach (static::$processes as $identifier => $process) {
                if (! $process->isRunning()) {
                    Qore::service(Operation::class)->getCache()->delete($identifier);
                }
            }

        } catch (\Throwable $e) {
            # - TODO Log this messages
            \dump($e);
            return false;
        }

        return true;
    }

    /**
     * Generate task process instance
     *
     * @param string $_identifier
     *
     * @return \Symfony\Component\Process\Process
     */
    protected function getProcess(string $_identifier): Process
    {
        return new Process(
            ['./assistant', 'operation:task-process', $_identifier],
            PROJECT_PATH
        );
    }

}
