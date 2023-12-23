<?php

declare(strict_types=1);

namespace Qore\QueueManager;

/**
 * Interface: JobInterface
 *
 */
interface JobInterface
{
    /**
     * setTask - назначаем задание
     *
     * @param mixed $_task
     */
    public function setTask($_task) : JobInterface;

    /**
     * getTask - возвращаем задание
     *
     */
    public function getTask();

    /**
     * process - исполняем задачу: true - задачу выполнили успешно; false - задачу провалили.
     *
     */
    public function process() : bool;

}
