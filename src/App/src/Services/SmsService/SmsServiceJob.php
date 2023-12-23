<?php

namespace Qore\App\Services\SmsService;

use Qore\Qore;
use Qore\QueueManager\JobAbstract;
use Qore\QueueManager\JobInterface;
use Qore\QueueManager\JobTrait;

/**
 * Class: SmsServiceJob
 *
 * @see JobInterface
 * @see JobAbstract
 */
class SmsServiceJob extends JobAbstract implements JobInterface
{
    use JobTrait;

    protected static $name = null;
    protected static $persistence = false;
    protected static $acknowledgement = true;
    protected static $workersNumber = 1;

    /**
     * process
     *
     */
    public function process() : bool
    {
        $smsService = Qore::service(SmsService::class);
        $smsService->send($this->task['phone'], sprintf('Ваш код %s', $this->task['code']));

        return true;
    }

}
