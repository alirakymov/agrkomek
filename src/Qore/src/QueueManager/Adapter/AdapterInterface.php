<?php

declare(strict_types=1);

namespace Qore\QueueManager\Adapter;

use Qore\QueueManager\JobInterface;

/**
 * Interface: AdapterInterface
 *
 */
interface AdapterInterface
{
    /**
     * publish
     *
     * @param JobInterface $_job
     */
    public function publish(JobInterface $_job);

    /**
     * subscribe
     *
     * @param string $_jobClass
     */
    public function subscribe(string $_jobClass);

    /**
     * prepareQueueName
     *
     * @param string|JobAbstract $_job
     */
    public function prepareQueueName($_job) : string;

}
