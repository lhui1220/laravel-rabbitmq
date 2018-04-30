<?php
/**
 * Created by PhpStorm.
 * User: lhui1
 * Date: 2018/4/30
 * Time: 12:51
 */

namespace GeekIO\Rabbit;

use Illuminate\Queue\Jobs\Job;
use Illuminate\Contracts\Queue\Job as JobContract;

class RabbitJob extends Job implements JobContract
{

    private $job;

    function __construct($container, $job)
    {
        $this->container = $container;
        $this->job = $job;
    }

    /**
     * Get the raw body of the job.
     *
     * @return string
     */
    public function getRawBody()
    {
        return $this->job;
    }

    /**
     * Get the number of times the job has been attempted.
     *
     * @return int
     */
    public function attempts()
    {
        //TODO 实现重试逻辑
        return 1;
    }
}