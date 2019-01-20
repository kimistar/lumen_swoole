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
    'daemonize' => 0,
    'dispatch_mode' => 3,
    'worker_num' => 4,
    'max_request' => 5000,
    'log_file' => storage_path('logs/swoole.log'),
    'log_level' => 5,
    'pid_file' => storage_path('logs/swoole.pid'),
    'open_tcp_nodelay' => 1,
    'heart_beat_internal' => 300,
];