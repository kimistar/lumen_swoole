## 安装
```
composer require kimistar/lumen_swoole
```

## 配置

在bootstrap/app.php中注册service provider
```
$app->register(Star\LumenSwoole\SwooleServiceProvider::class);
```

自定义配置文件覆盖默认配置，命名为swoole.php
```
return [
    'host' => env('SWOOLE_HOST','127.0.0.1'),
    'port' => env('SWOOLE_PORT',8080),
    'worker_num' => env('SWOOLE_WORKER_NUM',8),
    'task_worker_num' => env('SWOOLE_TASK_WORKER_NUM',2),
    //
];
```
同时在bootstrap/app.php加载此文件

```
$app->configure('swoole');
```

## 使用

```
php artisan sumen start | restart | stop | reload | status
```
默认监听127.0.0.1 8080端口，开启4个worker进程

注意无法reload的文件 @https://wiki.swoole.com/wiki/page/p-server/reload.html

包括但不限于
- bootstrap/app.php
- app/Providers/*
- config/*
- app/Console/*

投递任务至task worker进程
- 闭包
```
swoole_http()->task(\Closure $func,\Closure $callback);
```

配置Nginx

```nginx
server {
    listen 80;
    server_name your.domain.com;

    location / {
        try_files /_not_exists @swoole;
    }

    location @swoole {
        proxy_http_version 1.1;
        #proxy_set_header Connection "keep-alive";
        proxy_set_header Host $host;
        proxy_set_header SERVER_PORT $server_port;
        proxy_set_header REMOTE_ADDR $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Real-IP $remote_addr;
        # proxy_set_header HTTPS "on";

        proxy_pass http://127.0.0.1:8080;
    }
}
```

## ab测试

```
ab -c 100 -n 20000  http://api.swoole.cn/
This is ApacheBench, Version 2.3 <$Revision: 655654 $>
Copyright 1996 Adam Twiss, Zeus Technology Ltd, http://www.zeustech.net/
Licensed to The Apache Software Foundation, http://www.apache.org/
  
Benchmarking api.swoole.cn (be patient)
Completed 2000 requests
Completed 4000 requests
Completed 6000 requests
Completed 8000 requests
Completed 10000 requests
Completed 12000 requests
Completed 14000 requests
Completed 16000 requests
Completed 18000 requests
Completed 20000 requests
Finished 20000 requests
  
  
Server Software:        nginx/1.10.2
Server Hostname:        api.swoole.cn
Server Port:            80
  
Document Path:          /
Document Length:        9 bytes
  
Concurrency Level:      100
Time taken for tests:   2.373 seconds
Complete requests:      20000
Failed requests:        0
Write errors:           0
Total transferred:      6240000 bytes
HTML transferred:       180000 bytes
Requests per second:    8427.15 [#/sec] (mean)
Time per request:       11.866 [ms] (mean)
Time per request:       0.119 [ms] (mean, across all concurrent requests)
Transfer rate:          2567.65 [Kbytes/sec] received
  
Connection Times (ms)
              min  mean[+/-sd] median   max
Connect:        0    1   1.1      1       7
Processing:     1   11   5.2     10      40
Waiting:        1   10   5.1      9      40
Total:          1   12   5.2     11      41
  
Percentage of the requests served within a certain time (ms)
  50%     11
  66%     13
  75%     15
  80%     16
  90%     19
  95%     22
  98%     25
  99%     28
 100%     41 (longest request)
```
