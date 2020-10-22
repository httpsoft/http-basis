<?php

declare(strict_types=1);

namespace HttpSoft\Tests\Basis\Middleware;

use HttpSoft\Basis\Middleware\ContentLengthMiddleware;
use HttpSoft\Message\Response;
use HttpSoft\Message\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ContentLengthMiddlewareTest extends TestCase
{
    public function testProcess(): void
    {
        $middleware = new ContentLengthMiddleware();
        $response = $middleware->process(new ServerRequest(), new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $response = new Response();
                $response->getBody()->write('Content');
                return $response;
            }
        });
        $this->assertSame(7, $response->getBody()->getSize());
        $this->assertSame('7', $response->getHeaderLine('content-length'));
    }
}
