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
    public static function convert($request)
    {
        $newHeader = [];
        $swooleHeader = [];
        foreach ($request->header as $key => $value) {
            $newHeader[ucwords($key, '-')] = $value;
            $key = str_replace('-', '_', $key);
            $swooleHeader['http_' . $key] = $value;
        }
        $server = array_merge($request->server, $swooleHeader);

        // swoole has changed all keys to lower case
        $server = array_change_key_case($server, CASE_UPPER);
        $request->server = $server;
        $request->header = $newHeader;

        return $request;
    }

    /**
     * handle request with lumen app and return illuminate response
     * @param $request
     */
    public static function handle($request,$app)
    {
        ob_start();
        $illuminateRequest = self::convertRequest($request);
        try{
            $illuminateResponse = $app->handle($illuminateRequest);

            $content = $illuminateResponse->getContent();

            if (strlen($content) === 0 && ob_get_length() > 0) {
                $illuminateResponse->setContent(ob_get_contents());
            }
        }catch (\Exception $exception) {
            $illuminateResponse = (new Handler())->render($illuminateRequest,$exception);
        }

        ob_end_clean();

        return $illuminateResponse;
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

        return new IlluminateRequest($get, $post, []/* attributes */, $cookie, $files, $server, $content);
    }
}