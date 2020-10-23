<?php

declare(strict_types=1);

namespace HttpSoft\Tests\Basis\TestAsset;

use HttpSoft\Response\TextResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use StdClass;

class DummyHandler implements RequestHandlerInterface
{
    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return self::staticHandle();
    }

    /**
     * @return ResponseInterface
     */
    public static function staticHandle()
    {
        return new TextResponse(self::body());
    }

    /**
     * @return string
     */
    public static function body()
    {
        return 'Dummy Handler';
    }

    /**
     * @return null
     */
    public static function invalidStaticHandle()
    {
        return new StdClass();
    }

    /**
     * @return null
     */
    public function invalidHandle()
    {
        return null;
    }
}
