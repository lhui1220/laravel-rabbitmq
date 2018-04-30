<?php
/**
 * Created by PhpStorm.
 * User: lhui1
 * Date: 2018/4/29
 * Time: 20:05
 */

namespace GeekIO\Rabbit;

use Illuminate\Queue\Connectors\ConnectorInterface;
use PhpAmqpLib\Connection\AMQPStreamConnection;

/**
 * RabbitMQ连接器
 *
 * @author liuhui
 *
 * @package GeekIO\Rabbit
 */
class RabbitConnector implements ConnectorInterface
{

    /**
     * Establish a queue connection.
     *
     * @param  array $config
     * @return \Illuminate\Contracts\Queue\Queue
     */
    public function connect(array $config)
    {
        $connection = new AMQPStreamConnection(
            $config['host'],
            $config['port'],
            $config['user'],
            $config['password'],
            $config['vhost']
        );
        $queue = $config['queue']; //default queue
        return new RabbitQueue($connection, $queue);
    }
}