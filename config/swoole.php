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
    'worker_num' => 8,
    'task_worker_num' => 2,
    'task_max_request' => 2000,
    'log_file' => storage_path('logs/swoole_server.log'),
    'log_level' => 5,
    'pid_file' => storage_path('logs/swoole_server.pid'),
    'heartbeat_idle_time' => 300,
    'heartbeat_check_interval' => 30,
];