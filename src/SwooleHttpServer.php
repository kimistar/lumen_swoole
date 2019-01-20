<?php
/**
 * Created by PhpStorm.
 * User: kimistar
 * Date: 2018/1/20
 * Time: 15:47
 */

namespace Star\LumenSwoole;

use Laravel\Lumen\Application;

class SwooleHttpServer
{
    protected $config;
    protected $server;
    protected $app;

    public function __construct($swooleConfig)
    {
        $this->config = $swooleConfig;
        $this->server = new \swoole_http_server($this->config['host'], $this->config['port']);
    }

    public function run()
    {
        unset($this->config['host'], $this->config['port']);

        if (SWOOLE_VERSION >= '2.0') {
            $this->config['enable_coroutine'] = false;
        }
        #set swoole http server configuration
        $this->server->set($this->config);
        #set event listener
        $this->server->on('start', [$this, 'onStart']);
        $this->server->on('managerStart', [$this, 'onManagerStart']);
        $this->server->on('workerStart', [$this, 'onWorkerStart']);
        $this->server->on('request', [$this, 'onRequest']);
        #start swoole http server
        $this->server->start();
    }

    public function onStart()
    {
        if (PHP_OS == 'Linux') {
            swoole_set_process_name('swoole http master');
        }
    }

    public function onManagerStart()
    {
        if (PHP_OS == 'Linux') {
            swoole_set_process_name('swoole http manager');
        }
    }

    public function onWorkerStart(\swoole_http_server $server, $worker_id)
    {
        #maintain one lumen app instance in each worker process
        $this->app = Application::getInstance();

        if ($worker_id == 0) {
            if (extension_loaded('inotify')) {
                (new Inotify($this->server))->watch();
            }
        }

        if (PHP_OS == 'Linux') {
            swoole_set_process_name('swoole http worker');
        }

        // mysql redis 心跳检查 执行ping操作
        $server->tick(300 * 1000, function () {
            if ($this->app->heartBeat instanceof \Closure) {
                $this->app->heartBeat();
            }
        });
    }

    public function onRequest(\swoole_http_request $request, \swoole_http_response $response)
    {
        #convert swoole request headers and servers to normal request headers and servers
        $request = Request::convertServer($request);

        #handle request and return illuminate response
        $illuminateResponse = Request::handle($request, $this->app);

        #handle returned illuminate response
        Response::handle($response, $illuminateResponse);
    }
}