<?php
/**
 * Created by PhpStorm.
 * User: kimistar
 * Date: 2018/1/20
 * Time: 15:48
 */

namespace Star\LumenSwoole;

use Illuminate\Http\Request as IlluminateRequest;
use Laravel\Lumen\Exceptions\Handler;

class Request
{
    public static function convertServer($request)
    {
        $header = [];
        foreach ($request->header as $key => $value) {
            $key = str_replace('-', '_', $key);
            if (!in_array($key, ['server_port', 'remote_addr'])) {
                $header['http_' . $key] = $value;
            } else {
                $header[$key] = $value;
            }
        }
        $server = array_merge($request->server, $header);

        // swoole has changed all keys to lower case
        $server = array_change_key_case($server, CASE_UPPER);
        $request->server = $server;

        return $request;
    }

    /**
     * convert swoole request to illuminate request
     * @param $request
     * @return IlluminateRequest
     */
    public static function convertRequest($request)
    {
        $get = isset($request->get) ? $request->get : [];
        $post = isset($request->post) ? $request->post : [];
        $cookie = isset($request->cookie) ? $request->cookie : [];
        $server = isset($request->server) ? $request->server : [];
        $header = isset($request->header) ? $request->header : [];
        $files = isset($request->files) ? $request->files : [];

        $content = $request->rawContent() ?: null;

        return new IlluminateRequest($get, $post, $header, $cookie, $files, $server, $content);
    }

    /**
     * handle request with lumen app and return illuminate response
     * @param $request
     */
    public static function handle($request, $app)
    {
        ob_start();
        $illuminateRequest = self::convertRequest($request);
        try {
            $illuminateResponse = $app->handle($illuminateRequest);

            $content = $illuminateResponse->getContent();

            if (strlen($content) === 0 && ob_get_length() > 0) {
                $illuminateResponse->setContent(ob_get_contents());
            }
        } catch (\Exception $exception) {
            $illuminateResponse = (new Handler())->render($illuminateRequest, $exception);
        }

        ob_end_clean();

        return $illuminateResponse;
    }
}