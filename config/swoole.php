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
    'worker_num' => 4,
    'task_worker_num' => 1,
    'task_max_request' => 2000,
    'log_file' => storage_path('logs/swoole_server.log'),
    'log_level' => 5,
    'pid_file' => storage_path('logs/swoole_server.pid'),
    'heartbeat_idle_time' => 30,
    'heartbeat_check_interval' => 10,
    'open_tcp_nodelay' => 1,
];