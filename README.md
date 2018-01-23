## Speed up lumen5.5 api application by swoole extension

### Start
```
composer require kimistar/lumen_swoole
```

Register service provider in bootstrap/app.php
```
$app->register(Star\LumenSwoole\SwooleServiceProvider::class);
```

Configure your own config file named swoole.php in config dir
```
return [
    'host' => env('SWOOLE_HOST','127.0.0.1'),
    'port' => env('SWOOLE_PORT',8080),
    'options' => [
        'worker_num' => env('SWOOLE_WORKER_NUM',8),
        'max_request' => env('SWOOLE_MAX_REQUEST',2000),
        'dispatch_mode' => 3,
        'daemonize' => env('SWOOLE_DAEMONIZE',1),
        'log_file' => storage_path('logs/swoole_server.log'),
        'pid_file' => storage_path('logs/swoole_server.pid'),
    ],
];
```

You can start | restart | stop | reload the swoole http server by artisan command
```
php artisan swoole:http start | restart | stop | reload
```

As you can see,the server listens on 8080 port and runs 8 worker processes.

Then configure nginx server like this  @https://github.com/huang-yi/laravel-swoole-http
```nginx
server {
    listen 80;
    server_name your.domain.com;
    root /path/to/laravel/public;
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
            set $suffix "/";
        }
    
        proxy_set_header Host $host;
        proxy_set_header SERVER_PORT $server_port;
        proxy_set_header REMOTE_ADDR $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;

        # IF https
        # proxy_set_header HTTPS "on";

        proxy_pass http://127.0.0.1:1215$suffix;
    }
}
```
