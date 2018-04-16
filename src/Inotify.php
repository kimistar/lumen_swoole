<?php

namespace Star\LumenSwoole;

class Inotify
{
    protected $fd;
    protected $server;

    public function __construct($server)
    {
        $this->fd = inotify_init();
        $this->server = $server;
    }

    public function watch()
    {
        $appPath = base_path('/app');
        $this->addWatch($appPath);

        $flag = false;
        swoole_event_add($this->fd, function ($fd) use ($flag) {
            $events = inotify_read($this->fd);
            foreach ($events as $event) {
                if ($flag) {
                    break;
                }
                $this->server->reload();
                $flag = true;
            }
        });
    }

    private function addWatch($path)
    {
        inotify_add_watch($this->fd, $path, IN_CREATE | IN_MODIFY | IN_DELETE);
        if (is_dir($path)) {
            $files = scandir($path);
            foreach ($files as $item) {
                if ($item !== '.' && $item !== '..') {
                    $file = $path . DIRECTORY_SEPARATOR . $item;
                    if (is_dir($file)) {
                        $this->addWatch($file);
                    }

                    inotify_add_watch($this->fd, $file, IN_CREATE | IN_MODIFY | IN_DELETE);
                }
            }
        }
    }
}