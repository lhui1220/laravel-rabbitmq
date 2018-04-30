<?php
/**
 * Created by PhpStorm.
 * User: lhui1
 * Date: 2018/4/29
 * Time: 16:08
 */

namespace GeekIO\Rabbit;

use Illuminate\Queue\Queue;
use Illuminate\Contracts\Queue\Queue as QueueContract;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * 基于RabbitMQ实现Laravel队列
 *
 * @author liuhui
 * @date 2018-04-30
 *
 * @package GeekIO\Rabbit
 */
class RabbitQueue extends Queue implements QueueContract
{
    //AMQP Connection
    private $connection;

    //AMQP channel
    private $channel;

    //default queue name
    private $queue;

    function __construct(AbstractConnection $connection, $queue)
    {
        $this->connection = $connection;
        $this->channel = $connection->channel();
        $this->queue = $queue;
    }

    /**
     * Get the size of the queue.
     *
     * @param  string $queue
     * @return int
     */
    public function size($queue = null)
    {
        $info = $this->channel->basic_get($this->getQueue());
        return $info ? $info['message_count'] : 0;
    }

    /**
     * Push a new job onto the queue.
     *
     * @param  string|object $job
     * @param  mixed $data
     * @param  string $queue
     * @return mixed
     */
    public function push($job, $data = '', $queue = null)
    {
        $this->pushRaw($this->createPayload($job,$data),$queue);
    }

    /**
     * Push a raw payload onto the queue.
     *
     * @param  string $payload
     * @param  string $queue
     * @param  array $options
     * @return mixed
     */
    public function pushRaw($payload, $queue = null, array $options = [])
    {
        $msg = new AMQPMessage(
            $payload,
            ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]);
        $this->channel->basic_publish($msg, '', $this->getQueue($queue));
    }

    /**
     * Push a new job onto the queue after a delay.
     *
     * @param  \DateTimeInterface|\DateInterval|int $delay
     * @param  string|object $job
     * @param  mixed $data
     * @param  string $queue
     * @return mixed
     */
    public function later($delay, $job, $data = '', $queue = null)
    {
        // TODO: Implement later() method.
    }

    /**
     * Pop the next job off of the queue.
     *
     * @param  string $queue
     * @return \Illuminate\Contracts\Queue\Job|null
     */
    public function pop($queue = null)
    {
//        echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";
        $job = null;
        $callback = function($msg) use (&$job){
//            echo " [x] Received ", $msg->body, "\n";
            $job = new RabbitJob($this->container, $msg->body);
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };
        $consumer_tag = $this->channel->basic_consume($this->getQueue($queue), '', false, false, false, false, $callback);
        //NOTE: 由于laravel不支持异步获取Job,所以这里不能用while循环
        if (count($this->channel->callbacks)) {
            $this->channel->wait();
        }
        //NOTE: 由于每次循环都basic_consume会产生多个consumer_tag,所以这里得cancel掉,这样处理会有一点点性能影响
        $this->channel->basic_cancel($consumer_tag);
        return $job;
    }

    /**
     * Get the queue or return the default.
     *
     * @param  string|null  $queue
     * @return string
     */
    private function getQueue($queue)
    {
        return $queue ?: $this->queue;
    }
}