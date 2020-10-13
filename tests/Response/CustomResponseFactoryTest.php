<?php

declare(strict_types=1);

namespace HttpSoft\Tests\Basis\Response;

use HttpSoft\Basis\Response\CustomResponseFactory;
use HttpSoft\Message\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class CustomResponseFactoryTest extends TestCase
{
    public function testCreateResponseByDefault(): void
    {
        $factory = new CustomResponseFactory();
        $response = $factory->createResponse();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('OK', $response->getReasonPhrase());
        $this->assertInstanceOf(StreamInterface::class, $response->getBody());
        $this->assertSame('php://temp', $response->getBody()->getMetadata('uri'));
        $this->assertSame(['Content-Type' => ['text/html; charset=UTF-8']], $response->getHeaders());
        $this->assertSame('1.1', $response->getProtocolVersion());
    }

    public function testCreateResponseWithPassingDataToConstructor(): void
    {
        $factory = new CustomResponseFactory(['Content-Type' => 'application/json; charset=UTF-8']);
        $response = $factory->createResponse($code = 404);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame($code, $response->getStatusCode());
        $this->assertSame('Not Found', $response->getReasonPhrase());
        $this->assertInstanceOf(StreamInterface::class, $response->getBody());
        $this->assertSame('php://temp', $response->getBody()->getMetadata('uri'));
        $this->assertSame(['Content-Type' => ['application/json; charset=UTF-8']], $response->getHeaders());
        $this->assertSame('1.1', $response->getProtocolVersion());

        $factory = new CustomResponseFactory([], 'php://memory', '2');
        $response = $factory->createResponse($code = 404, $customPhrase = 'Custom Phrase');
        $this->assertInstanceOf(Response::class, $response);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame($code, $response->getStatusCode());
        $this->assertSame($customPhrase, $response->getReasonPhrase());
        $this->assertInstanceOf(StreamInterface::class, $response->getBody());
        $this->assertSame('php://memory', $response->getBody()->getMetadata('uri'));
        $this->assertSame([], $response->getHeaders());
        $this->assertSame('2', $response->getProtocolVersion());
    }
}
