<?php
/**
 * Created by PhpStorm.
 * Author: kimistar
 * Date: 2018/3/15
 * Time: 13:37
 */
if (!function_exists('swoole_http')) {
    function swoole_http()
    {
        return app()->make('swoole.http');
    }
}