<?php

declare(strict_types=1);

namespace HttpSoft\Tests\Basis\TestAsset;

use HttpSoft\Basis\Exception\HttpException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ErrorMiddleware implements MiddlewareInterface
{
    /**
     * @var HttpException
     */
    private HttpException $exception;

    /**
     * @param HttpException $exception
     */
    public function __construct(HttpException $exception)
    {
        $this->exception = $exception;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws HttpException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        throw $this->exception;
    }
}
