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
    protected $signature = 'sumen {action : how to handle the server}';

    protected $description = '开启|关闭|重启|重载swoole http server';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $action = $this->argument('action');
        switch ($action) {
            case 'start':
                $this->start();
                break;
            case 'restart':
                $this->restart();
                break;
            case 'reload':
                $this->reload();
                break;
            case 'stop':
                $this->stop();
                break;
            case 'status':
                $this->status();
                break;
            case 'vendor:publish':
                $this->vendorPublish();
                break;
            default:
                $this->info(
                    'Please type correct action.'.PHP_EOL.
                    '  start'.PHP_EOL.
                    '  stop'.PHP_EOL.
                    '  restart'.PHP_EOL.
                    '  reload'.PHP_EOL.
                    '  status'.PHP_EOL.
                    '  vendor:publish'.PHP_EOL
                );
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
        $this->info('stopping swoole http server...');
        $pid = $this->sendSignal(SIGTERM);
        $time = 0;
        while (posix_getpgid($pid)) {
            usleep(100000);
            $time++;
            if ($time > 50) {
                $this->error('timeout...');
                exit(1);
            }
        }
        $this->info('done');
        $this->start();
    }

    protected function reload()
    {
        $this->info('reloading...');
        $this->sendSignal(SIGUSR1);
        $this->info('done');
    }

    protected function stop()
    {
        $this->info('immediately stopping...');
        $this->sendSignal(SIGTERM);
        $this->info('done');
    }

    protected function status()
    {
        $pid = $this->getPid();
        if ($pid) {
            $this->info('swoole http server is running. master pid : ' . $pid);
        } else {
            $this->error('swoole http server is not running!');
        }
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
        return $pid;
    }

    protected function getPid()
    {
        $pid_file = config('swoole.pid_file');
        if (file_exists($pid_file)) {
            $pid = intval(file_get_contents($pid_file));
            if (posix_getpgid($pid)) {
                return $pid;
            } else {
                unlink($pid_file);
            }
        }
        return false;
    }

    protected function vendorPublish()
    {
        $source = __DIR__ . '/../config/swoole.php';
        if (!is_dir(base_path('config'))) {
            mkdir(base_path('config'));
        }
        $dst = base_path('config/swoole.php');
        copy($source, $dst);

        $this->info('success');
    }
}