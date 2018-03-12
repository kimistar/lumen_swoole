<?php
/**
 * Created by PhpStorm.
 * User: kimistar
 * Date: 2018/1/20
 * Time: 15:50
 */
namespace Star\LumenSwoole;

use Illuminate\Console\Command as IlluminateCommand;

class Command extends IlluminateCommand
{
    protected $signature = 'swoole:http {action : start | restart | stop | reload | status}';

    protected $description = 'swoole http server';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        if (!extension_loaded('swoole')) {
            $this->error('First of all,you must install swoole extension!');
        }
        $action = $this->argument('action');
        switch ($action) {
            case 'start':
                $this->start();
                break;
            case 'restart':
                $this->restart();
                break;
            case 'stop':
                $this->stop();
                break;
            case 'reload':
                $this->reload();
                break;
            case 'status':
                $this->status();
                break;
            default:
                $this->error('Please type correct action . start | restart | stop | reload | status');
        }
    }

    protected function start()
    {
        if ($this->getPid()) {
            $this->error('swoole http server is already running');
            exit(1);
        }

        $this->info('starting swoole http server...');
        app()->make('swoole.http')->run();
    }

    protected function restart()
    {
        $this->stop();
        sleep(1);
        $this->start();
    }

    protected function stop()
    {
        $this->info('stopping...');
        $this->sendSignal(SIGTERM);
        $this->info('stopped');
    }

    protected function reload()
    {
        $this->info('reloading...');
        $this->sendSignal(SIGUSR1);
        $this->info('reloaded');
    }

    protected function status()
    {

    }

    protected function sendSignal($sig)
    {
        $pid = $this->getPid();
        if ($pid) {
            posix_kill($pid, $sig);
        } else {
            $this->error('swoole http is not running!');
            exit(1);
        }
    }

    protected function getPid()
    {
        $pid_file = config('swoole.options.pid_file');
        if (file_exists($pid_file)) {
            $pid = file_get_contents($pid_file);
            if (posix_getpgid($pid)) {
                return $pid;
            } else {
                unlink($pid_file);
            }
        }
        return false;
    }
}