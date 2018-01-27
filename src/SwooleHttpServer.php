<?php
/**
 * Created by PhpStorm.
 * User: kimistar
 * Date: 2018/1/20
 * Time: 15:47
 */
namespace Star\LumenSwoole;

class SwooleHttpServer
{
    protected $config;
    protected $server;
    protected $app;

    public function __construct($swooleConfig)
    {
        $this->config = $swooleConfig;
        $this->server = new \swoole_http_server($this->config['host'],$this->config['port']);
    }

    public function run()
    {
        #set swoole http server configuration
        $this->server->set($this->config['options']);
        #set event listener
        $this->server->on('start',[$this,'onStart']);
        $this->server->on('managerStart',[$this,'onManagerStart']);
        $this->server->on('workerStart',[$this,'onWorkerStart']);
        $this->server->on('request',[$this,'onRequest']);
        #start swoole http server
        $this->server->start();
    }

    public function onStart()
    {
        #set master process name
        swoole_set_process_name('swoole http master');
    }

    public function onManagerStart()
    {
        #set manager process name
        swoole_set_process_name('swoole http manager');
    }

    public function onWorkerStart()
    {
        #maintain one lumen app instance in each worker process
        $this->app = require base_path('bootstrap/app.php');
        #set worker process name
        swoole_set_process_name('swoole http worker');
    }

    public function onRequest(\swoole_http_request $request,\swoole_http_response $response)
    {
        #convert swoole request headers and servers to normal request headers and servers
        $request = Request::convert($request);

        #handle request and return illuminate response
        $illuminateResponse = Request::handle($request,$this->app);

        #handle returned illuminate response
        Response::handle($response,$illuminateResponse);
    }
}