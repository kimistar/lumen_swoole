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
    public function boot()
    {

    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/swoole.php', 'swoole'
        );

        $this->commands([
            Command::class,
        ]);

        $this->app->singleton('swoole.http',function($app) {
            $swooleConfig = $app->make('config')->get('swoole');
            return new SwooleHttpServer($swooleConfig);
        });
    }
}