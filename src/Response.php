<?php
/**
 * Created by PhpStorm.
 * User: kimistar
 * Date: 2018/1/20
 * Time: 15:49
 */

namespace Star\LumenSwoole;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

class Response
{
    public static function handle($swooleResponse, $illuminateResponse)
    {
        // status
        $swooleResponse->status($illuminateResponse->getStatusCode());
        foreach ($illuminateResponse->headers->allPreserveCase() as $name => $values) {
            foreach ($values as $value) {
                $swooleResponse->header($name, $value);
            }
        }
        // cookies
        foreach ($illuminateResponse->headers->getCookies() as $cookie) {
            $swooleResponse->rawcookie(
                $cookie->getName(),
                urlencode($cookie->getValue()),
                $cookie->getExpiresTime(),
                $cookie->getPath(),
                $cookie->getDomain(),
                $cookie->isSecure(),
                $cookie->isHttpOnly()
            );
        }
        // content
        if ($illuminateResponse instanceof BinaryFileResponse) {
            $realPath = realpath($illuminateResponse->getFile()->getPathname());
            $swooleResponse->sendfile($realPath);
        } else {
            $content = $illuminateResponse->getContent();
            $swooleResponse->end($content);
        }
    }
}