<?php
/**
 * Created by PhpStorm.
 * User: kimistar
 * Date: 2018/1/20
 * Time: 15:47
 */
namespace Star\LumenSwoole;

use Laravel\Lumen\Application;
use SuperClosure\SerializableClosure;

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

    public function run($daemon = false)
    {
        if ($daemon) {
            $this->config['options']['daemonize'] = 1;
        } else {
            $this->config['options']['daemonize'] = 0;
        }
        #set swoole http server configuration
        $this->server->set($this->config['options']);
        #set event listener
        $this->server->on('start',[$this,'onStart']);
        $this->server->on('managerStart',[$this,'onManagerStart']);
        $this->server->on('workerStart',[$this,'onWorkerStart']);
        $this->server->on('task',[$this,'onTask']);
        $this->server->on('finish',[$this,'onFinish']);
        $this->server->on('request',[$this,'onRequest']);
        #start swoole http server
        $this->server->start();
    }

    public function task($class,$method = '',$parmas = [])
    {
        if ($class instanceof \Closure) {
            $this->server->task(serialize(new SerializableClosure($class)));
        }
        if (is_string($class)) {
            if (strpos($class,'/') !== false) {
                $class = str_replace('/',"\\",$class);
            }
            $fullClass = "\\App\\Http\\Tasks\\".$class;
            $this->server->task([
                'class' => $fullClass,
                'method' => $method,
                'params' => $parmas,
            ]);
        }
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

    public function onWorkerStart(\swoole_http_server $server,$worker_id)
    {
        #maintain one lumen app instance in each worker process
        $this->app = Application::getInstance();
        #set worker process name
        if($worker_id < $server->setting['worker_num']) {
            swoole_set_process_name('swoole http worker');
        }else {
            swoole_set_process_name('swoole http task worker');
        }
    }

    public function onTask(\swoole_http_server $serv,$task_id,$src_work_id,$data)
    {
        if (is_string($data)) {
            $func = unserialize($data);
            $func();
        }
        if (is_array($data)) {
            $obj = new $data['class']();
            $method = $data['method'];
            $params = $data['params'];

            if (is_callable([$obj,$method])) {
                call_user_func_array([$obj,$method],$params);
            }
        }
    }

    public function onFinish(\swoole_http_server $serv,$task_id,$data)
    {

    }

    public function onRequest(\swoole_http_request $request,\swoole_http_response $response)
    {
        #unset global variables on each request
        unset($_GET,$_POST,$_SERVER,$_COOKIE,$_FILES);
        #convert swoole request headers and servers to normal request headers and servers
        $request = Request::convertServer($request);

        #build global variables
        $this->buildGlobals($request);

        #handle request and return illuminate response
        $illuminateResponse = Request::handle($request,$this->app);

        #handle returned illuminate response
        Response::handle($response,$illuminateResponse);
    }

    protected function buildGlobals($request)
    {
        $_GET = $_POST = $_COOKIE = $_FILES = [];

        foreach ($request->server as $key => $value) {
            $_SERVER[$key] = $value;
        }
        if (isset($request->get)) {
            $_GET = $request->get;
        }
        if (isset($request->post)) {
            $_POST = $request->post;
        }
        if (isset($request->cookie)) {
            $_COOKIE = $request->cookie;
        }
        if (isset($request->files)) {
            $_FILES = $request->files;
        }
    }
}