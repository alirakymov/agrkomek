<?php

declare(strict_types=1);

namespace Qore\QueueManager\Adapter;

use PhpAmqpLib\Channel\AMQPChannel;
use Qore\QueueManager\JobAbstract;
use Qore\QueueManager\JobInterface;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Throwable;

/**
 * Class: Amqp
 *
 * @see AdapterInterface
 */
class AmqpAdapter implements AdapterInterface
{
    /**
     * config
     *
     * @var array
     */
    private $config = [];

    /**
     * connection
     *
     * @var mixed
     */
    private $connection = null;

    /**
     * channels
     *
     * @var mixed
     */
    private $channels = [];

    /**
     * Constructor 
     *
     */
    public function __construct(array $_config)
    {
        $defaultConfig = [
            'host' => 'localhost',
            'port' => '5672',
            'username' => 'guest',
            'password' => 'guest',
        ];

        $this->config = array_merge($defaultConfig, $_config);
    }

    /**
     * publish
     *
     * @param JobInterface $_job
     * @return void 
     */
    public function publish(JobInterface $_job) : void
    {
        $this->getChannel($_job)->basic_publish($this->getMessage($_job), '', $this->prepareQueueName($_job));
    }

    /**
     * subscribe
     *
     * @param string $_jobClass
     * @return void 
     */
    public function subscribe(string $_jobClass) : void
    {
        $callback = function($_message) {
            # - Get job
            $job = $this->unserialize($_message->getBody());

            if ($job instanceof Throwable) {
                # - [TODO] LOG it
                $_message->ack();
                return;
            }

            # - Start job process
            $result = $job->process($_message);
            # - Check result with acknowledgement
            if ($job::withAcknowledgement()) {
                $result ? $_message->ack() : $_message->nack(true);
            }
        };

        # - Get channel
        $channel = $this->getChannel($_jobClass);
        # - Set rule for RMQ: 1 worker = 1 Task
        $channel->basic_qos(null, 1, null);
        # - Subscribe for consume
        $channel->basic_consume($this->prepareQueueName($_jobClass), '', false, ! $_jobClass::withAcknowledgement(), false, false, $callback);
        # - Wait for job
        while ($channel->is_consuming()) {
            $channel->wait();
        }
    }

    /**
     * prepareQueueName
     *
     * @param string|JobAbstract $_job
     * @return string
     */
    public function prepareQueueName($_job) : string
    {
        return preg_replace('/[^a-z0-9]/i', '_', $_job::getQueueName()) . '_' . (int)$_job::isPersistent() . '_' . (int)$_job::withAcknowledgement();
    }

    /**
     * getChannel
     *
     * @param string|JobAbstract $_job
     * @return \PhpAmqpLib\Channel\AMQPChannel 
     */
    private function getChannel($_job) : AMQPChannel
    {
        $queueName = $this->prepareQueueName($_job);

        if (! isset($this->channels[$queueName])) {
            $this->declareQueue($_job, $channel = $this->getConnection()->channel());
            $this->channels[$queueName] = $channel;
        }

        return $this->channels[$queueName];
    }

    /**
     * Get connection instance to AMQP provider
     *
     * @return \PhpAmqpLib\Connection\AMQPStreamConnection
     */
    private function getConnection() : AMQPStreamConnection
    {
        if (is_null($this->connection)) {
            $this->connection = new AMQPStreamConnection(
                $this->config['host'], $this->config['port'],
                $this->config['username'], $this->config['password']
            );
        }

        return $this->connection;
    }

    /**
     * declareQueue
     *
     * @param JobAbstract|stirng $_job
     * @param AMQPChannel $_channel
     * @return void 
     */
    private function declareQueue($_job, AMQPChannel $_channel) : void
    {
        $_channel->queue_declare($this->prepareQueueName($_job), false, $_job::isPersistent(), false, false);
    }

    /**
     * getMessage
     *
     * @param JobInterface $_job
     * @return \PhpAmqpLib\Message\AMQPMessage 
     */
    private function getMessage(JobInterface $_job) : AMQPMessage
    {
        $options = $_job::isPersistent() ? ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT] : [];
        return new AMQPMessage($this->serialize($_job), $options);
    }

    /**
     * serialize
     *
     * @param \Qore\QueueManager\JobInterface $_job
     * @return string 
     */
    private function serialize(JobInterface $_job) : string
    {
        return serialize($_job);
    }

    /**
     * unserialize
     *
     * @param string $_job
     * @return \Qore\QueueManager\JobInterface 
     */
    private function unserialize(string $_job) : JobInterface
    {
        try {
            return unserialize($_job);
        } catch (Throwable $e) {
            # TODO log https://www.php-fig.org/psr/psr-3/
        }
    }

    /**
     * Destructor
     *
     */
    public function __destruct()
    {
        foreach ($this->channels as $channel) {
            $channel->close();
        }

        $this->connection && $this->connection->close();
    }

}
