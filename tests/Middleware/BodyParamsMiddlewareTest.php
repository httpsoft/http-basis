<?php

declare(strict_types=1);

namespace HttpSoft\Tests\Basis\Middleware;

use HttpSoft\Basis\Exception\BadRequestHttpException;
use HttpSoft\Basis\Middleware\BodyParamsMiddleware;
use HttpSoft\Message\Response;
use HttpSoft\Message\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function rtrim;

class BodyParamsMiddlewareTest extends TestCase
{
    /**
     * @var BodyParamsMiddleware
     */
    private BodyParamsMiddleware $middleware;

    public function setUp(): void
    {
        $this->middleware = new BodyParamsMiddleware();
    }

    public function testProcessWithNotEmptyParsedBody(): void
    {
        $parsedBody = ['name' => 'value'];

        $request = $this->createServerRequest('POST', 'application/json', $parsedBody);
        $request->getBody()->write('{"body":"content"}');
        $response = $this->middleware->process($request, $this->createRequestHandler());
        $this->assertSame(($this->parsedBodyToString())($parsedBody), (string) $response->getBody());

        $request = $this->createServerRequest('PATCH', 'application/*+json', $parsedBody);
        $request->getBody()->write('{"body":"content"}');
        $response = $this->middleware->process($request, $this->createRequestHandler());
        $this->assertSame(($this->parsedBodyToString())($parsedBody), (string) $response->getBody());

        $request = $this->createServerRequest('PUT', 'application/x-www-form-urlencoded', $parsedBody);
        $request->getBody()->write('body=content');
        $response = $this->middleware->process($request, $this->createRequestHandler());
        $this->assertSame(($this->parsedBodyToString())($parsedBody), (string) $response->getBody());
    }

    public function testProcessWithMethodsForNonBodyRequest(): void
    {
        $request = $this->createServerRequest('GET', 'application/json');
        $response = $this->middleware->process($request, $this->createRequestHandler());
        $this->assertSame('', (string) $response->getBody());

        $request = $this->createServerRequest('HEAD', 'application/*+json');
        $response = $this->middleware->process($request, $this->createRequestHandler());
        $this->assertSame('', (string) $response->getBody());

        $request = $this->createServerRequest('OPTIONS', 'application/x-www-form-urlencoded');
        $response = $this->middleware->process($request, $this->createRequestHandler());
        $this->assertSame('', (string) $response->getBody());
    }

    /**
     * @return array[]
     */
    public function matchDataProvider(): array
    {
        return [
            'form' => [
                'application/x-www-form-urlencoded',
                'name=value',
                'name:value',
            ],
            'form-with-charset' => [
                'application/x-www-form-urlencoded ; charset=UTF-8',
                'name1=value1&name2=value2',
                'name1:value1,name2:value2',
            ],
            'json' => [
                "application/json ",
                '{"name":"value"}',
                'name:value',
            ],
            'json-with-charset' => [
                "application/json; charset=UTF-8 ",
                '{"name":"value"}',
                'name:value',
            ],
            'json-suffix' => [
                'application/vnd.api+json;charset=UTF-8',
                '{"name":"value"}',
                'name:value',
            ],
        ];
    }

    /**
     * @dataProvider matchDataProvider
     * @param string $contentType
     * @param string $requestBody
     * @param string $expectedBody
     * @throws BadRequestHttpException
     */
    public function testProcessWithMatchData(string $contentType, string $requestBody, string $expectedBody)
    {
        $request = $this->createServerRequest('POST', $contentType);
        $request->getBody()->write($requestBody);
        $response = $this->middleware->process($request, $this->createRequestHandler());
        $this->assertSame($expectedBody, (string) $response->getBody());
    }

    /**
     * @return array[]
     */
    public function mismatchDataProvider(): array
    {
        return [
            'html-content-type' => [
                'text/html',
                '<h1>name</h1>',
            ],
            'text-content-type' => [
                'text/plain',
                'name:value',
            ],
            'xml-content-type' => [
                'application/xml',
                '<name>name</name>',
            ],
            'empty-content-type' => [
                '',
                '{"name":"value"}',
            ],
            'unknown-content-type' => [
                'application/name+value',
                '{"name":"value"}',
            ],
            'invalid-content-type' => [
                'name',
                '{"name":"value"}',
            ],
            'invalid-json-content-type' => [
                "application/ json",
                '{"name":"value"}',
            ],
            'invalid-form-content-type' => [
                'application/ x-www-form-urlencoded',
                'name=value',
            ],
        ];
    }

    /**
     * @dataProvider mismatchDataProvider
     * @param string $contentType
     * @param string $requestBody
     * @throws BadRequestHttpException
     */
    public function testProcessWithMismatchData(string $contentType, string $requestBody)
    {
        $request = $this->createServerRequest('POST', $contentType);
        $request->getBody()->write($requestBody);
        $response = $this->middleware->process($request, $this->createRequestHandler());
        $this->assertSame('', (string) $response->getBody());
    }

    public function testProcessThrowBadRequestHttpExceptionForInvalidJsonBody(): void
    {
        $request = $this->createServerRequest('POST', 'application/json');
        $request->getBody()->write('{"name"}/value');
        $this->expectException(BadRequestHttpException::class);
        $this->middleware->process($request, $this->createRequestHandler());
    }

    /**
     * @param string $method
     * @param string|null $contentType
     * @param null|array|object $parsedBody
     * @return ServerRequestInterface
     */
    private function createServerRequest(
        string $method,
        string $contentType = null,
        $parsedBody = null
    ): ServerRequestInterface {
        $headers = ($contentType === null) ? [] : ['Content-Type' => $contentType];
        return new ServerRequest([], [], [], [], $parsedBody, $method, 'https://example.com', $headers);
    }

    /**
     * @return RequestHandlerInterface
     */
    private function createRequestHandler(): RequestHandlerInterface
    {
        return new class ($this->parsedBodyToString()) implements RequestHandlerInterface {
            public $callback;

            public function __construct(callable $callback)
            {
                $this->callback = $callback;
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $response = new Response();
                $response->getBody()->write(($this->callback)($request->getParsedBody()));
                return $response;
            }
        };
    }

    /**
     * @return callable
     */
    private function parsedBodyToString(): callable
    {
        return static function (?array $parsedBody): string {
            $content = '';

            foreach ((array) $parsedBody as $name => $value) {
                $content .= "{$name}:{$value},";
            }

            return rtrim($content, ',');
        };
    }
}
