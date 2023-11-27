<?php

namespace Qore\SynapseManager\Plugin\Report;

use Qore\Qore;
use Qore\QueueManager\JobAbstract;
use Qore\QueueManager\JobInterface;
use Qore\QueueManager\JobTrait;
use Qore\SynapseManager\SynapseManager;
use Throwable;

/**
 * Class: ReportJob
 *
 * @see JobInterface
 * @see JobAbstract
 */
class ReportJob extends JobAbstract implements JobInterface
{
    use JobTrait;

    protected static $name = null;
    protected static $persistence = false;
    protected static $acknowledgement = true;
    protected static $workersNumber = 1;

    /**
     * Process
     *
     * @return bool
     */
    public function process() : bool
    {
        try {
            /** @var SynapseManager */
            $sm = Qore::service(SynapseManager::class);
            $artificer = $sm($this->task['artificer']);

            $reportService = $artificer->plugin(Report::class);
            return $reportService->make($this->task);

        } catch (Throwable $e) {
            dump($e);
            return false;
        }
    }

}
