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
        $this->server = new \swoole_http_server($this->config['host'], $this->config['port']);
    }

    public function run($daemon = false)
    {
        unset($this->config['host'], $this->config['port']);

        if ($daemon) {
            $this->config['daemonize'] = 1;
        } else {
            $this->config['daemonize'] = 0;
        }
        #set swoole http server configuration
        $this->server->set($this->config);
        #set event listener
        $this->server->on('start', [$this, 'onStart']);
        $this->server->on('managerStart', [$this, 'onManagerStart']);
        $this->server->on('workerStart', [$this, 'onWorkerStart']);
        if (isset($this->config['task_worker_num'])) {
            $this->server->on('task', [$this, 'onTask']);
            $this->server->on('finish', [$this, 'onFinish']);
        }
        $this->server->on('request', [$this, 'onRequest']);
        #start swoole http server
        $this->server->start();
    }

    /**
     * @param $func | 投递的闭包
     * @param null $callback | 回调
     */
    public function task($func, $callback = null)
    {
        if ($func instanceof \Closure) {
            $data = [
                'func' => serialize(new SerializableClosure($func)),
                'callback' => null,
            ];
            if ($callback instanceof \Closure) {
                $data['callback'] = serialize(new SerializableClosure($callback));
            }
            $this->server->task($data);
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

    public function onWorkerStart(\swoole_http_server $server, $worker_id)
    {
        #maintain one lumen app instance in each worker process
        $this->app = Application::getInstance();

        if ($worker_id == 0) {
            if (extension_loaded('inotify')) {
                (new Inotify($this->server))->watch();
            }
        }

        #set worker process name
        if ($worker_id < $server->setting['worker_num']) {
            swoole_set_process_name('swoole http worker');
        } else {
            swoole_set_process_name('swoole http task worker');
        }
    }

    public function onTask(\swoole_http_server $serv, $task_id, $src_work_id, $data)
    {
        $func = unserialize($data['func']);
        $func();
        if (!is_null($data['callback'])) {
            $this->server->finish($data['callback']);
        }
    }

    public function onFinish(\swoole_http_server $serv, $task_id, $data)
    {
        $callback = unserialize($data);
        $callback();
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