## 安装
```
composer require kimistar/lumen_swoole
```

## 配置

在bootstrap/app.php中注册service provider
```
$app->register(Star\LumenSwoole\SwooleServiceProvider::class);
```

可以配置自定义配置文件，命名为swoole.php
```
return [
    'host' => env('SWOOLE_HOST','127.0.0.1'),
    'port' => env('SWOOLE_PORT',8080),
    'options' => [
        'worker_num' => env('SWOOLE_WORKER_NUM',8),
        'task_worker_num' => env('SWOOLE_TASK_WORKER_NUM',2),
        'max_request' => env('SWOOLE_MAX_REQUEST',2000),
        /*
        |--------------------------
        |@https://wiki.swoole.com/wiki/page/277.html
        |1 轮循模式
        |2 固定模式
        |3 抢占模式
        |4 IP分配
        |5 UID分配
        |--------------------------
        |无状态Server可以使用1或3，同步阻塞Server使用3，异步非阻塞Server使用1
        |有状态使用2、4、5
         */
        'dispatch_mode' => env('SWOOLE_DISPATCH_MODE',3),
        'log_file' => storage_path('logs/swoole_server.log'),
        'log_level' => env('SWOOLE_LOG_LEVEL',1), 
        'pid_file' => storage_path('logs/swoole_server.pid'),
    ],
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

其他操作

```
php artisan swoole:http restart | stop | reload | status
```

注意无法reload的文件 @https://wiki.swoole.com/wiki/page/p-server/reload.html

默认情况下，监听127.0.0.1 8080端口，开启8个worker进程 2个task worker进程

### 配置Nginx @https://github.com/huang-yi/laravel-swoole-http/blob/master/README.md

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
