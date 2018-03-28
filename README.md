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

启动swoole http server --d以守护进程方式启动
```
php artisan sumen start --d
```
默认监听127.0.0.1 8080端口，开启4个worker进程 1个task worker进程

其他 重启/停止/重载/状态

```
php artisan sumen restart | stop | reload | status
```

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
$callback：任务执行完毕后的回调
```

配置Nginx @https://github.com/huang-yi/laravel-swoole-http/blob/master/README.md

```nginx
server {
    listen 80;
    server_name your.domain.com;
    root /path/to/lumen/public;
    index index.php;

    location = /index.php {
        # Ensure that there is no such file named "not_exists" in your "public" directory.
        try_files /not_exists @swoole;
    }

    location / {
        try_files $uri $uri/ @swoole;
    }

    location @swoole {
        set $suffix "";
        
        if ($uri = /index.php) {
            set $suffix "/?${query_string}";
        }
    
        proxy_http_version 1.1;
        proxy_set_header Connection "keep-alive";
        proxy_set_header Host $host;
        proxy_set_header SERVER_PORT $server_port;
        proxy_set_header REMOTE_ADDR $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Real-IP $remote_addr;

        # IF https
        # proxy_set_header HTTPS "on";

        proxy_pass http://127.0.0.1:8080$suffix;
    }
}
```

## ab测试
 
swoole http server

```
ab -c 100 -n 1000 -k http://api.swoole.com/
This is ApacheBench, Version 2.3 <$Revision: 1706008 $>
Copyright 1996 Adam Twiss, Zeus Technology Ltd, http://www.zeustech.net/
Licensed to The Apache Software Foundation, http://www.apache.org/
  
Benchmarking api.swoole.com (be patient)
Completed 100 requests
Completed 200 requests
Completed 300 requests
Completed 400 requests
Completed 500 requests
Completed 600 requests
Completed 700 requests
Completed 800 requests
Completed 900 requests
Completed 1000 requests
Finished 1000 requests
  
  
Server Software:        nginx
Server Hostname:        api.swoole.com
Server Port:            80
  
Document Path:          /
Document Length:        11 bytes
  
Concurrency Level:      100
Time taken for tests:   0.921 seconds
Complete requests:      1000
Failed requests:        0
Keep-Alive requests:    1000
Total transferred:      185000 bytes
HTML transferred:       11000 bytes
Requests per second:    1085.93 [#/sec] (mean)
Time per request:       92.087 [ms] (mean)
Time per request:       0.921 [ms] (mean, across all concurrent requests)
Transfer rate:          196.19 [Kbytes/sec] received
  
Connection Times (ms)
              min  mean[+/-sd] median   max
Connect:        0    0   0.5      0       3
Processing:    71   91   7.5     91     107
Waiting:       71   91   7.5     91     107
Total:         71   92   7.8     91     110
  
Percentage of the requests served within a certain time (ms)
  50%     91
  66%     93
  75%     96
  80%     96
  90%    107
  95%    108
  98%    109
  99%    109
 100%    110 (longest request)
```

php-fpm
```
ab -c 100 -n 1000 -k http://api.lumen.com/
This is ApacheBench, Version 2.3 <$Revision: 1706008 $>
Copyright 1996 Adam Twiss, Zeus Technology Ltd, http://www.zeustech.net/
Licensed to The Apache Software Foundation, http://www.apache.org/
  
Benchmarking api.lumen.com (be patient)
Completed 100 requests
Completed 200 requests
Completed 300 requests
Completed 400 requests
Completed 500 requests
Completed 600 requests
Completed 700 requests
Completed 800 requests
Completed 900 requests
Completed 1000 requests
Finished 1000 requests
  
Server Software:        nginx
Server Hostname:        api.lumen.com
Server Port:            80
  
Document Path:          /
Document Length:        11 bytes
  
Concurrency Level:      100
Time taken for tests:   47.174 seconds
Complete requests:      1000
Failed requests:        0
Keep-Alive requests:    0
Total transferred:      175000 bytes
HTML transferred:       11000 bytes
Requests per second:    21.20 [#/sec] (mean)
Time per request:       4717.403 [ms] (mean)
Time per request:       47.174 [ms] (mean, across all concurrent requests)
Transfer rate:          3.62 [Kbytes/sec] received
   
Connection Times (ms)
              min  mean[+/-sd] median   max
Connect:        0    0   1.1      0       5
Processing:   444 4520 761.4   4669    5386
Waiting:      444 4520 761.4   4669    5386
Total:        449 4521 760.4   4669    5388
  
Percentage of the requests served within a certain time (ms)
  50%   4669
  66%   4743
  75%   4832
  80%   4872
  90%   4897
  95%   4986
  98%   5186
  99%   5295
 100%   5388 (longest request)
```
