<?php

declare(strict_types=1);

namespace Qore\QueueManager;

/**
 * Trait: JobTrait
 */
trait JobTrait
{
    /**
     * getQueueName - возвращает имя очереди в которой должна будет выполнена данная задача
     *
     */
    public static function getQueueName() : string
    {
        return sprintf(
            '%s-%s',
            defined(PROJECT_PATH) ? PROJECT_PATH : 'NoneUniquePreffix',
            static::$name ?? static::class
        );
    }

    /**
     * isPersistent - если true значит задача обязательна, ждем повышенной ответственности от брокера
     *
     */
    public static function isPersistent() : bool
    {
        return static::$persistence;
    }

    /**
     * withAcknowledgement - если true значит просим брокера убедиться в том что задача исполнена
     *
     */
    public static function withAcknowledgement() : bool
    {
        return static::$acknowledgement;
    }

    /**
     * getWorkersNumber - возвращаем количество исполнителей под эту задачу
     *
     */
    public static function getWorkersNumber() : int
    {
        return static::$workersNumber;
    }

    /**
     * getWorkerCommand - команда которая отвечает за запуск исполнителя в сервисе
     *      null - стандартная команда
     *
     */
    public static function getWorkerCommand() : ?string
    {
        return null;
    }

}
