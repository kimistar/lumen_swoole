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
php artisan swoole:http start --d
```
默认监听127.0.0.1 8080端口，开启8个worker进程 2个task worker进程


其他 重启/停止/重载/状态

```
php artisan swoole:http restart | stop | reload | status
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
swoole_http()->task(function() {
    //code
});
```
- (\App\Http\Tasks\Class)类/方法/参数
```
swoole_http()->task('Class','method',$params = []);
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
ab -c 100 -n 10000 -k http://api.swoole.cn/
This is ApacheBench, Version 2.3 <$Revision: 1706008 $>
Copyright 1996 Adam Twiss, Zeus Technology Ltd, http://www.zeustech.net/
Licensed to The Apache Software Foundation, http://www.apache.org/
  
Benchmarking api.swoole.cn (be patient)
Completed 1000 requests
Completed 2000 requests
Completed 3000 requests
Completed 4000 requests
Completed 5000 requests
Completed 6000 requests
Completed 7000 requests
Completed 8000 requests
Completed 9000 requests
Completed 10000 requests
Finished 10000 requests
  
Server Software:        nginx/1.13.3
Server Hostname:        api.swoole.cn
Server Port:            80
  
Document Path:          /
Document Length:        0 bytes
  
Concurrency Level:      100
Time taken for tests:   9.260 seconds
Complete requests:      10000
Failed requests:        0
Keep-Alive requests:    9904
Total transferred:      2119520 bytes
HTML transferred:       0 bytes
Requests per second:    1079.86 [#/sec] (mean)
Time per request:       92.604 [ms] (mean)
Time per request:       0.926 [ms] (mean, across all concurrent requests)
Transfer rate:          223.51 [Kbytes/sec] received
  
Connection Times (ms)
              min  mean[+/-sd] median   max
Connect:        0    0   0.1      0       1
Processing:    48   92  16.2     91     268
Waiting:       48   92  16.2     91     268
Total:         48   92  16.3     91     268
  
Percentage of the requests served within a certain time (ms)
  50%     91
  66%     92
  75%     93
  80%     94
  90%     97
  95%     99
  98%    102
  99%    210
 100%    268 (longest request)
```

php-fpm
```
ab -c 100 -n 10000 -k http://api.fpm.cn/
This is ApacheBench, Version 2.3 <$Revision: 1706008 $>
Copyright 1996 Adam Twiss, Zeus Technology Ltd, http://www.zeustech.net/
Licensed to The Apache Software Foundation, http://www.apache.org/
  
Benchmarking api.fpm.cn (be patient)
Completed 1000 requests
Completed 2000 requests
Completed 3000 requests
Completed 4000 requests
Completed 5000 requests
Completed 6000 requests
Completed 7000 requests
Completed 8000 requests
Completed 9000 requests
Completed 10000 requests
Finished 10000 requests
  
Server Software:        nginx/1.13.3
Server Hostname:        api.fpm.cn
Server Port:            80
  
Document Path:          /
Document Length:        11 bytes
  
Concurrency Level:      100
Time taken for tests:   700.850 seconds
Complete requests:      10000
Failed requests:        0
Keep-Alive requests:    0
Total transferred:      2140000 bytes
HTML transferred:       110000 bytes
Requests per second:    14.27 [#/sec] (mean)
Time per request:       7008.500 [ms] (mean)
Time per request:       70.085 [ms] (mean, across all concurrent requests)
Transfer rate:          2.98 [Kbytes/sec] received
  
Connection Times (ms)
              min  mean[+/-sd] median   max
Connect:        0    0   0.1      0       2
Processing:   462 6976 378.7   6982    7585
Waiting:      462 6976 378.7   6982    7585
Total:        463 6976 378.7   6982    7585
  
Percentage of the requests served within a certain time (ms)
  50%   6982
  66%   7018
  75%   7050
  80%   7074
  90%   7164
  95%   7238
  98%   7310
  99%   7381
 100%   7585 (longest request)
```
