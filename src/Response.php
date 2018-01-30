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
    public static function handle($swooleReponse,$illuminateResponse)
    {
        // status
        $swooleReponse->status($illuminateResponse->getStatusCode());
        foreach ($illuminateResponse->headers->allPreserveCase() as $name => $values) {
            foreach ($values as $value) {
                $swooleReponse->header($name, $value);
            }
        }
        // cookies
        foreach ($illuminateResponse->headers->getCookies() as $cookie) {
            $swooleReponse->rawcookie(
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
            $content = function () use ($illuminateResponse) {
                return $illuminateResponse->getFile()->getPathname();
            };
        } else {
            $content = $illuminateResponse->getContent();
        }
        self::end($swooleReponse,$content);
    }

    public static function end($swooleResponse,$content)
    {
        if (!is_string($content)) {
            $swooleResponse->sendfile(realpath($content()));
        } else {
            // send content & close
            $swooleResponse->end($content);
        }
    }
}