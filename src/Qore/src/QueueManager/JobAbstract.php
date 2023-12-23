<?php

declare(strict_types=1);

namespace Qore\QueueManager;

/**
 * Class: AbstractJob
 *
 * @abstract
 */
abstract class JobAbstract implements JobInterface
{
    /**
     * task
     *
     * @var mixed
     */
    protected $task = null;

    /**
     * __construct
     *
     * @param mixed $_task
     */
    public function __construct($_task = null)
    {
        $this->task = $_task;
    }

    /**
     * setTask
     *
     * @param mixed $_task
     */
    public function setTask($_task) : JobInterface
    {
        $this->task = $_task;
        return $this;
    }

    /**
     * getTask
     *
     */
    public function getTask()
    {
        return $this->task;
    }

    /**
     * getQueueName - возвращает имя очереди в которой должна будет выполнена данная задача
     *
     */
    abstract public static function getQueueName() : string;

    /**
     * isPersistent - если true значит задача обязательна, ждем повышенной ответственности от брокера
     *
     */
    abstract public static function isPersistent() : bool;

    /**
     * withAcknowledgement - если true значит просим брокера убедиться в том что задача исполнена
     *
     * @param bool $_value
     */
    abstract public static function withAcknowledgement() : bool;

    /**
     * getWorkersNumber - возвращаем количество исполнителей под эту задачу
     *
     */
    abstract public static function getWorkersNumber() : int;

    /**
     * getWorkerCommand - команда которая отвечает за запуск исполнителя в сервисе
     *      null - стандартная команда
     *
     */
    abstract public static function getWorkerCommand() : ?string;

}
