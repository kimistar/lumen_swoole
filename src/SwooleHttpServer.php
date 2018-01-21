<?php
/**
 * Created by PhpStorm.
 * User: kimistar
 * Date: 2018/1/20
 * Time: 15:47
 */
namespace Star\LumenSwoole;

use Swoole\Http\Server;

class SwooleHttpServer
{
    protected $config;
    protected $server;
    protected $app;

    public function __construct($app)
    {
        $this->app = $app;
        $this->config = $app->make('config')->get('swoole');
        $this->server = new Server($this->config['host'],$this->config['port']);
    }

    public function run()
    {
        $this->server->set($this->config['options']);
        $this->server->on('start',[$this,'onStart']);
        $this->server->on('managerStart',[$this,'onManagerStart']);
        $this->server->on('workerStart',[$this,'onWorkerStart']);
        $this->server->on('request',[$this,'onRequest']);
        $this->server->start();
    }

    public function onStart()
    {
        swoole_set_process_name('swoole http server master');
    }

    public function onWorkerStart()
    {
        swoole_set_process_name('swoole http server worker');
        //$this->app = require base_path('bootstrap/app.php');
    }

    public function onManagerStart()
    {
        swoole_set_process_name('swoole http server manager');
    }

    public function onRequest(\swoole_http_request $request,\swoole_http_response $response)
    {
        //convert swoole headers and server
        $request = Request::convertHeaders($request);

        //handle request and return illuminate response
        $illuminateResponse = Request::handle($request,$this->app);

        //handle illuminate response
        Response::handle($response,$illuminateResponse);
    }
}