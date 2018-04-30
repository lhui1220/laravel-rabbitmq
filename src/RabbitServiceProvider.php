<?php
/**
 * Created by PhpStorm.
 * User: lhui1
 * Date: 2018/4/29
 * Time: 16:04
 */

namespace GeekIO\Rabbit;

use Illuminate\Queue\QueueManager;
use Illuminate\Support\ServiceProvider;

/**
 * RabbitMQ队列服务
 *
 * @author liuhui
 * @date 2018-04-30
 *
 * @package GeekIO\Rabbit
 */
class RabbitServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/rabbit.php','rabbit');
    }

    public function boot()
    {
        //添加rabbitmq连接器
        $this->app[QueueManager::class]->addConnector('rabbit', function () {
            return new RabbitConnector();
        });

        //发布配置信息
        $this->publishes([
            __DIR__.'/../config/rabbit.php' => config_path('rabbit.php')
        ],'rabbit');

    }
}