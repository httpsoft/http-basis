<?php

declare(strict_types=1);

namespace HttpSoft\Basis\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class ContentLengthMiddleware implements MiddlewareInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        $size = $response->getBody()->getSize();

        if ($size !== null && !$response->hasHeader('content-length')) {
            $response = $response->withHeader('content-length', (string) $size);
        }

        return $response;
    }
}
