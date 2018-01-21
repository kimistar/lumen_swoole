<?php
/**
 * Created by PhpStorm.
 * User: kimistar
 * Date: 2018/1/20
 * Time: 18:21
 */
return [
    'host' => '',
    'port' => 8080,
    'options' => [
        'worker_num' => 8,
        'max_request' => 2000,
        'dispatch_mode' => 3,
        'daemonize' => 0,
        'log_file' => storage_path('logs/swoole_server.log'),
        'heartbeat_check_interval' => 60,
        'heartbeat_idle_time' => 300,
        'pid_file' => storage_path('logs/swoole_server.pid'),
    ],
];