<?php

declare(strict_types=1);

namespace HttpSoft\Basis\Middleware;

use JsonException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function json_decode;
use function in_array;
use function parse_str;
use function preg_match;

final class BodyParamsMiddleware implements MiddlewareInterface
{
    /**
     * {@inheritDoc}
     *
     * @throws JsonException
     * @link https://tools.ietf.org/html/rfc7231
     * @psalm-suppress MixedAssignment
     * @psalm-suppress MixedArgument
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->getParsedBody() || in_array($request->getMethod(), ['GET', 'HEAD', 'OPTIONS'], true)) {
            return $handler->handle($request);
        }

        $contentType = $request->getHeaderLine('content-type');

        if (preg_match('#^application/(|[\S]+\+)json($|[ ;])#', $contentType)) {
            $parsedBody = json_decode((string) $request->getBody(), true, 512, JSON_THROW_ON_ERROR);
        } elseif (preg_match('#^application/x-www-form-urlencoded($|[ ;])#', $contentType)) {
            parse_str((string) $request->getBody(), $parsedBody);
        }

        return $handler->handle(empty($parsedBody) ? $request : $request->withParsedBody($parsedBody));
    }
}
