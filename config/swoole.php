<?php
/**
 * Created by PhpStorm.
 * User: kimistar
 * Date: 2018/1/20
 * Time: 18:21
 */
return [
    'host' => '127.0.0.1',
    'port' => 8080,
    'options' => [
        'worker_num' => 8,
        'task_worker_num' => 2,
        'max_request' => 2000,
        /*
        |--------------------------
        |1 轮循模式
        |2 固定模式
        |3 抢占模式
        |4 IP分配
        |5 UID分配
        |--------------------------
        |无状态Server可以使用1或3，同步阻塞Server使用3，异步非阻塞Server使用1
        |有状态使用2、4、5
         */
        'dispatch_mode' => 3,
        'daemonize' => 1,
        'log_file' => storage_path('logs/swoole_server.log'),
        'log_level' => 5,
        'pid_file' => storage_path('logs/swoole_server.pid'),
    ],
];