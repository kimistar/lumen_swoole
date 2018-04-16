<?php
/**
 * Created by PhpStorm.
 * User: kimistar
 * Date: 2018/1/20
 * Time: 15:50
 */

namespace Star\LumenSwoole;

use Illuminate\Support\ServiceProvider;

class SwooleServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->commands([
            Command::class,
        ]);

        $this->app->singleton('swoole.http', function ($app) {
            $this->mergeConfigFrom(
                __DIR__ . '/../config/swoole.php', 'swoole'
            );

            $swooleConfig = $app['config']['swoole'];
            return new SwooleHttpServer($swooleConfig);
        });
    }
}