<?php

declare(strict_types=1);

namespace HttpSoft\Basis\Response;

use HttpSoft\Message\Response;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

final class CustomResponseFactory implements ResponseFactoryInterface
{
    /**
     * @var array
     */
    private array $headers;

    /**
     * @var StreamInterface|string|resource
     */
    private $body;

    /**
     * @var string
     */
    private string $protocol;

    /**
     * @param array|null $headers
     * @param StreamInterface|string|resource $body
     * @param string $protocol
     */
    public function __construct(?array $headers = null, $body = 'php://temp', string $protocol = '1.1')
    {
        $this->headers = $headers ?? ['Content-Type' => 'text/html; charset=UTF-8'];
        $this->body = $body;
        $this->protocol = $protocol;
    }

    /**
     * {@inheritDoc}
     */
    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        return new Response($code, $this->headers, $this->body, $this->protocol, $reasonPhrase);
    }
}
