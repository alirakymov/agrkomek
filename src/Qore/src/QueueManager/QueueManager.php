<?php

declare(strict_types=1);

namespace Qore\QueueManager;

use Qore\QueueManager\Adapter\AdapterInterface;

/**
 * Class: QueueManager
 *
 */
class QueueManager
{
    /**
     * _adapter
     *
     * @var AdapterInterface
     */
    protected AdapterInterface $adapter;
    

    /**
     * __construct
     *
     * @param AdapterInterface $_adapter
     */
    public function __construct(AdapterInterface $_adapter)
    {
        $this->adapter = $_adapter;
    }

    /**
     * Publish
     *
     * @param JobInterface $_job
     * @return void
     */
    public function publish(JobInterface $_job) : void
    {
        $this->adapter->publish($_job);
    }

    /**
     * Subscribe
     *
     * @param string $_jobClass 
     *
     * @return void
     */
    public function subscribe(string $_jobClass) : void
    {
        $this->adapter->subscribe($_jobClass);
    }

    /**
     * Retrive adapter instance
     *
     * @return \Qore\QueueManager\Adapter\AdapterInterface
     */
    public function getAdapter() : AdapterInterface
    {
        return $this->adapter;
    }

}
