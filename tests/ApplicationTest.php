<?php

declare(strict_types=1);

namespace HttpSoft\Tests\Basis;

use Devanych\Di\Container;
use HttpSoft\Basis\Application;
use HttpSoft\Basis\ErrorHandler\ErrorJsonResponseGenerator;
use HttpSoft\Basis\ErrorHandler\ErrorResponseGenerator;
use HttpSoft\Basis\Exception\HttpException;
use HttpSoft\Basis\Exception\InternalServerErrorHttpException;
use HttpSoft\Basis\Handler\NotFoundHandler;
use HttpSoft\Basis\Handler\NotFoundJsonHandler;
use HttpSoft\Basis\Middleware\BodyParamsMiddleware;
use HttpSoft\Basis\Middleware\ContentLengthMiddleware;
use HttpSoft\Basis\Response\CustomResponseFactory;
use HttpSoft\Basis\TemplateRendererInterface;
use HttpSoft\Emitter\EmitterInterface;
use HttpSoft\ErrorHandler\ErrorHandlerMiddleware;
use HttpSoft\ErrorHandler\ErrorResponseGeneratorInterface;
use HttpSoft\Message\Response;
use HttpSoft\Message\ServerRequestFactory;
use HttpSoft\Response\TextResponse;
use HttpSoft\Router\Middleware\RouteDispatchMiddleware;
use HttpSoft\Router\Middleware\RouteMatchMiddleware;
use HttpSoft\Router\RouteCollector;
use HttpSoft\Runner\Exception\InvalidMiddlewareResolverHandlerException;
use HttpSoft\Runner\MiddlewarePipeline;
use HttpSoft\Runner\MiddlewarePipelineInterface;
use HttpSoft\Runner\MiddlewareResolver;
use HttpSoft\Runner\MiddlewareResolverInterface;
use HttpSoft\Tests\Basis\TestAsset\DummyEmitter;
use HttpSoft\Tests\Basis\TestAsset\DummyHandler;
use HttpSoft\Tests\Basis\TestAsset\ErrorMiddleware;
use HttpSoft\Tests\Basis\TestAsset\TemplateRenderer;
use HttpSoft\Tests\Basis\TestAsset\TraitMethodsWrapper;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function get_class;
use function json_encode;
use function trim;

class ApplicationTest extends TestCase
{
    /**
     * @var TraitMethodsWrapper
     */
    private TraitMethodsWrapper $traits;

    public function setUp(): void
    {
        $this->traits = new TraitMethodsWrapper();
    }

    /**
     * @return array
     */
    public function debugDataProvider(): array
    {
        return [
            'debug-true' => [true],
            'debug-false' => [false]
        ];
    }

    /**
     * @dataProvider debugDataProvider
     * @param bool $debug
     */
    public function testRunWithoutPipingAndWithoutDefaultHandler(bool $debug): void
    {
        $container = $this->createContainer($debug);
        $app = $container->get(Application::class);
        $app->get('home', '/', $this->createCallableHandler());
        $app->run($request = $this->createServerRequest('GET', 'https://example.com'));
        $this->assertSame($this->notFoundJsonBody($request, $debug), $this->emittedBody($container));
    }

    /**
     * @dataProvider debugDataProvider
     * @param bool $debug
     */
    public function testRunWithoutPipingAndWithDefaultHandler(bool $debug): void
    {
        $container = $this->createContainer($debug);
        $app = $container->get(Application::class);
        $app->get('home', '/', $this->createCallableHandler());
        $app->run($request = $this->createServerRequest('GET', '/'), new DummyHandler());
        $this->assertSame(DummyHandler::body(), $this->emittedBody($container));
    }

    /**
     * @dataProvider debugDataProvider
     * @param bool $debug
     */
    public function testRunWithPipingAndWithoutDefaultHandler(bool $debug): void
    {
        $container = $this->createContainer($debug);
        $app = $this->createApplicationWithPipes($container);
        $app->get('home', '/', $this->createCallableHandler());
        $app->run($request = $this->createServerRequest('GET', 'https://example.com'));
        $this->assertSame('Callable Handler', $this->emittedBody($container));
    }

    /**
     * @dataProvider debugDataProvider
     * @param bool $debug
     */
    public function testRunWithPipingAndWithDefaultHandler(bool $debug): void
    {
        $container = $this->createContainer($debug);
        $app = $this->createApplicationWithPipes($container);
        $app->get('home', '/', $this->createCallableHandler());
        $app->run($request = $this->createServerRequest('GET', '/'), new DummyHandler());
        $this->assertSame('Callable Handler', $this->emittedBody($container));
    }

    /**
     * @dataProvider debugDataProvider
     * @param bool $debug
     */
    public function testRunWithPipingAndWithoutDefaultHandlerAndNotMatchedRoute(bool $debug): void
    {
        $container = $this->createContainer($debug);
        $app = $this->createApplicationWithPipes($container);
        $app->get('home', '/', $this->createCallableHandler());
        $app->run($request = $this->createServerRequest('GET', 'https://example.com/path'));
        $this->assertSame($this->notFoundJsonBody($request, $debug), $this->emittedBody($container));
    }

    /**
     * @dataProvider debugDataProvider
     * @param bool $debug
     */
    public function testRunWithPipingAndWithDefaultHandlerAndNotMatchedRoute(bool $debug): void
    {
        $container = $this->createContainer($debug);
        $app = $this->createApplicationWithPipes($container);
        $app->get('home', '/', $this->createCallableHandler());
        $app->run($request = $this->createServerRequest('GET', '/path'), new DummyHandler());
        $this->assertSame(DummyHandler::body(), $this->emittedBody($container));
    }

    /**
     * @dataProvider debugDataProvider
     * @param bool $debug
     */
    public function testRunWithPipingAndWithoutDefaultHandlerAndWithError(bool $debug): void
    {
        $container = $this->createContainer($debug);
        $error = new InternalServerErrorHttpException();
        $app = $this->createApplicationWithPipes($container, $error);
        $app->get('home', '/', $this->createCallableHandler());
        $app->run($request = $this->createServerRequest('GET', 'https://example.com'));
        $this->assertSame($this->errorJsonBody($request, $error, $debug), $this->emittedBody($container));
    }

    /**
     * @dataProvider debugDataProvider
     * @param bool $debug
     */
    public function testRunWithPipingAndWithDefaultHandlerAndWithError(bool $debug): void
    {
        $container = $this->createContainer($debug);
        $error = new InternalServerErrorHttpException();
        $app = $this->createApplicationWithPipes($container, $error);
        $app->get('home', '/', $this->createCallableHandler());
        $app->run($request = $this->createServerRequest('GET', '/'), new DummyHandler());
        $this->assertSame($this->errorJsonBody($request, $error, $debug), $this->emittedBody($container));
    }

    /**
     * @dataProvider debugDataProvider
     * @param bool $debug
     */
    public function testRunWithoutPipingAndWithoutDefaultHandlerAndWithTemplate(bool $debug): void
    {
        $container = $this->createContainer($debug, false);
        $app = $container->get(Application::class);
        $renderer = $container->get(TemplateRendererInterface::class);
        $app->get('home', '/', $this->createCallableHandler());
        $app->run($request = $this->createServerRequest('GET', 'https://example.com'));
        $this->assertSame($this->notFoundBody($request, $renderer, $debug), $this->emittedBody($container));
    }

    /**
     * @dataProvider debugDataProvider
     * @param bool $debug
     */
    public function testRunWithoutPipingAndWithDefaultHandlerAndWithTemplate(bool $debug): void
    {
        $container = $this->createContainer($debug, false);
        $app = $container->get(Application::class);
        $app->get('home', '/', $this->createCallableHandler());
        $app->run($request = $this->createServerRequest('GET', '/'), new DummyHandler());
        $this->assertSame(DummyHandler::body(), $this->emittedBody($container));
    }

    /**
     * @dataProvider debugDataProvider
     * @param bool $debug
     */
    public function testRunWithPipingAndWithoutDefaultHandlerAndWithTemplate(bool $debug): void
    {
        $container = $this->createContainer($debug, false);
        $app = $this->createApplicationWithPipes($container);
        $app->get('home', '/', $this->createCallableHandler());
        $app->run($request = $this->createServerRequest('GET', 'https://example.com'));
        $this->assertSame('Callable Handler', $this->emittedBody($container));
    }

    /**
     * @dataProvider debugDataProvider
     * @param bool $debug
     */
    public function testRunWithPipingAndWithDefaultHandlerAndWithTemplate(bool $debug): void
    {
        $container = $this->createContainer($debug, false);
        $app = $this->createApplicationWithPipes($container);
        $app->get('home', '/', $this->createCallableHandler());
        $app->run($request = $this->createServerRequest('GET', '/'), new DummyHandler());
        $this->assertSame('Callable Handler', $this->emittedBody($container));
    }

    /**
     * @dataProvider debugDataProvider
     * @param bool $debug
     */
    public function testRunWithPipingAndWithoutDefaultHandlerAndNotMatchedRouteAndWithTemplate(bool $debug): void
    {
        $container = $this->createContainer($debug, false);
        $app = $this->createApplicationWithPipes($container);
        $renderer = $container->get(TemplateRendererInterface::class);
        $app->get('home', '/', $this->createCallableHandler());
        $app->run($request = $this->createServerRequest('GET', 'https://example.com/path'));
        $this->assertSame($this->notFoundBody($request, $renderer, $debug), $this->emittedBody($container));
    }

    /**
     * @dataProvider debugDataProvider
     * @param bool $debug
     */
    public function testRunWithPipingAndWithDefaultHandlerAndNotMatchedRouteAndWithTemplate(bool $debug): void
    {
        $container = $this->createContainer($debug, false);
        $app = $this->createApplicationWithPipes($container);
        $app->get('home', '/', $this->createCallableHandler());
        $app->run($request = $this->createServerRequest('GET', '/path'), new DummyHandler());
        $this->assertSame(DummyHandler::body(), $this->emittedBody($container));
    }

    /**
     * @dataProvider debugDataProvider
     * @param bool $debug
     */
    public function testRunWithPipingAndWithoutDefaultHandlerAndWithErrorAndWithTemplate(bool $debug): void
    {
        $container = $this->createContainer($debug, false);
        $error = new InternalServerErrorHttpException();
        $app = $this->createApplicationWithPipes($container, $error);
        $renderer = $container->get(TemplateRendererInterface::class);
        $app->get('home', '/', $this->createCallableHandler());
        $app->run($request = $this->createServerRequest('GET', 'https://example.com'));
        $this->assertSame($this->errorBody($request, $renderer, $error, $debug), $this->emittedBody($container));
    }

    /**
     * @dataProvider debugDataProvider
     * @param bool $debug
     */
    public function testRunWithPipingAndWithDefaultHandlerAndWithErrorAndWithTemplate(bool $debug): void
    {
        $container = $this->createContainer($debug, false);
        $error = new InternalServerErrorHttpException();
        $app = $this->createApplicationWithPipes($container, $error);
        $renderer = $container->get(TemplateRendererInterface::class);
        $app->get('home', '/', $this->createCallableHandler());
        $app->run($request = $this->createServerRequest('GET', '/'), new DummyHandler());
        $this->assertSame($this->errorBody($request, $renderer, $error, $debug), $this->emittedBody($container));
    }

    /**
     * @return array
     */
    public function validMiddlewareProvider(): array
    {
        return [
            'middleware-class' => [ErrorHandlerMiddleware::class],
            'middleware-object' => [new ContentLengthMiddleware()],
            'request-handler-class' => [DummyHandler::class],
            'request-handler-object' => [new DummyHandler()],
            'callable-without-args' => [
                fn(): ResponseInterface => DummyHandler::staticHandle(),
            ],
            'callable-with-signature-as-request-handler-handle' => [
                fn(ServerRequestInterface $request): ResponseInterface => DummyHandler::staticHandle(),
            ],
            'callable-with-signature-as-middleware-process' => [
                function (ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
                    return $handler->handle($request);
                },
            ],
            'array-callable' => [
                [new DummyHandler(), 'handler'],
            ],
            'array-static-callable' => [
                [DummyHandler::class, 'staticHandler'],
            ],
            'array-middleware-classes' => [
                [
                    ErrorHandlerMiddleware::class,
                    BodyParamsMiddleware::class,
                    ContentLengthMiddleware::class,
                ],
            ],
            'array-middleware-request-handle-classes' => [
                [
                    ErrorHandlerMiddleware::class,
                    BodyParamsMiddleware::class,
                    ContentLengthMiddleware::class,
                    DummyHandler::class,
                ],
            ],
            'array-middleware-objects' => [
                [
                    new ErrorHandlerMiddleware(),
                    new BodyParamsMiddleware(),
                    new ContentLengthMiddleware(),
                ],
            ],
            'array-middleware-request-handle-objects' => [
                [
                    new ErrorHandlerMiddleware(),
                    new BodyParamsMiddleware(),
                    new ContentLengthMiddleware(),
                    new DummyHandler(),
                ],
            ],
            'array-middleware-request-handle-classes-objects' => [
                [
                    new ErrorHandlerMiddleware(),
                    BodyParamsMiddleware::class,
                    new ContentLengthMiddleware(),
                    DummyHandler::class,
                ],
            ],

            'array-middleware-callable-request-classes-objects' => [
                [
                    ErrorHandlerMiddleware::class,
                    [new BodyParamsMiddleware(), 'process'],
                    function (ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
                        return (new ContentLengthMiddleware())->process($request, $handler);
                    }
                ],
            ],
        ];
    }

    /**
     * @dataProvider validMiddlewareProvider
     * @param mixed $middleware
     */
    public function testPipeMiddleware($middleware): void
    {
        $container = $this->createContainer();
        $app = $container->get(Application::class);
        $app->pipe($middleware);
        $app->pipe(RouteMatchMiddleware::class);
        $app->pipe(RouteDispatchMiddleware::class);
        $app->get('home', '/', DummyHandler::class);
        $app->run($request = $this->createServerRequest('GET', 'https://example.com'));
        $this->assertSame(DummyHandler::body(), $this->emittedBody($container));
    }

    /**
     * @return array
     */
    public function invalidMiddlewareProvider(): array
    {
        return [
            'null' => [null],
            'true' => [true],
            'false' => [false],
            'integer' => [1],
            'float' => [1.1],
            'empty-array' => [[]],
            'object-not-middleware-request-handler' => [new DummyEmitter()],
            'array-item-not-middleware-or-request-handle-objects' => [
                [
                    new BodyParamsMiddleware(),
                    new DummyEmitter(),
                    new ContentLengthMiddleware(),
                ],
            ],
        ];
    }

    /**
     * @dataProvider invalidMiddlewareProvider
     * @param mixed $middleware
     */
    public function testPipeThrowExceptionForInvalidMiddleware($middleware): void
    {
        $container = $this->createContainer();
        $app = $container->get(Application::class);
        $this->expectException(InvalidMiddlewareResolverHandlerException::class);
        $app->pipe($middleware);
    }

    /**
     * @return array
     */
    public function invalidStringOrCallableMiddlewareProvider(): array
    {
        return [
            'string' => ['string'],
            'class-not-exist' => ['Class\Not\Exist'],
            'class-not-middleware-request-handler' => [DummyEmitter::class],
            'array-item-not-middleware-or-request-handle-classes' => [
                [
                    BodyParamsMiddleware::class,
                    DummyEmitter::class,
                    ContentLengthMiddleware::class,
                ],
            ],
            'callable-without-args-not-returns-ResponseInterface' => [fn() => null],
            'callable-with-signature-as-request-handler-handle-not-returns-ResponseInterface' => [
                fn(ServerRequestInterface $request) => $request,
            ],
            'callable-with-signature-as-middleware-process-not-returns-ResponseInterface' => [
                function (ServerRequestInterface $request, RequestHandlerInterface $handler) {
                    return $request ?: $handler;
                },
            ],
            'array-callable-without-args-not-returns-ResponseInterface' => [
                [new DummyHandler(), 'invalidHandle'],
            ],
            'array-static-callable-without-args-not-returns-ResponseInterface' => [
                [DummyHandler::class, 'invalidStaticHandle'],
            ],
        ];
    }

    /**
     * @dataProvider invalidStringOrCallableMiddlewareProvider
     * @param mixed $middleware
     */
    public function testPipeAndRunThrowExceptionForInvalidStringMiddleware($middleware): void
    {
        $container = $this->createContainer();
        $app = $container->get(Application::class);
        $app->pipe($middleware);
        $this->expectException(InvalidMiddlewareResolverHandlerException::class);
        $app->run($request = $this->createServerRequest('GET', 'https://example.com'));
    }

    /**
     * @return array
     */
    public function matchesPathPrefixProvider(): array
    {
        return [
            'empty' => ['', '/foo'],
            'empty-trailing-slash' => ['', '/foo/'],
            'empty-nested-path' => ['', '/foo/bar'],
            'slash' => ['/', '/foo'],
            'slash-trailing-slash' => ['/', '/foo/'],
            'slash-nested-path' => ['/', '/foo/bar'],
            'path-not-slash' => ['/foo', '/foo'],
            'path-trailing-slash' => ['/foo/', '/foo/'],
            'path-one-nested-path' => ['foo', '/foo/bar'],
            'path-two-nested-path' => ['foo', '/foo/bar/baz/'],
            'path-nested-path-file' => ['foo', '/foo/bar/file.txt'],
            'nested-path-one-nested-path' => ['foo/bar/', '/foo/bar'],
            'nested-path-two-nested-path' => ['foo/bar', '/foo/bar/baz/'],
        ];
    }

    /**
     * @dataProvider matchesPathPrefixProvider
     * @param string $pathPrefix
     * @param string $requestUriPath
     */
    public function testPipeMiddlewareMatchesPathPrefix(string $pathPrefix, string $requestUriPath): void
    {
        $container = $this->createContainer();
        $app = $container->get(Application::class);
        $app->pipe(ContentLengthMiddleware::class, $pathPrefix);
        $app->pipe(RouteMatchMiddleware::class);
        $app->pipe(RouteDispatchMiddleware::class);
        $app->get('page', $requestUriPath, $this->createContentLengthRequestHandler());
        $app->run($request = $this->createServerRequest('GET', 'https://example.com' . $requestUriPath));
        $this->assertSame($request->getHeaderLine('content-length'), $this->emittedBody($container));
    }

    /**
     * @return array
     */
    public function notMatchesPathPrefixProvider(): array
    {
        return [
            'not-equal-not-slash' => ['foo', '/bar'],
            'not-equal-trailing-slash' => ['foo/', '/bar/'],
            'not-equal-path-boundaries' => ['/foo', '/foobar'],
            'not-equal-nested' => ['/foo/bar', '/foo/baz'],
            'not-equal-one-nested' => ['/foo/bar/', '/foo'],
            'not-equal-two-nested' => ['/foo/bar/baz', '/foo/bar/'],
        ];
    }

    /**
     * @dataProvider notMatchesPathPrefixProvider
     * @param string $pathPrefix
     * @param string $requestUriPath
     */
    public function testPipeMiddlewareNotMatchesPathPrefix(string $pathPrefix, string $requestUriPath): void
    {
        $container = $this->createContainer();
        $app = $container->get(Application::class);
        $app->pipe(ContentLengthMiddleware::class, $pathPrefix);
        $app->pipe(RouteMatchMiddleware::class);
        $app->pipe(RouteDispatchMiddleware::class);
        $app->get('page', $requestUriPath, $this->createContentLengthRequestHandler());
        $app->run($request = $this->createServerRequest('GET', 'https://example.com' . $requestUriPath));
        $this->assertSame('', $this->emittedBody($container));
    }

    public function testAdd(): void
    {
        $container = $this->createContainer();
        $app = $container->get(Application::class);
        $route = $app->add($name = 'test', $pattern = '/path', DummyHandler::class, $methods = ['GET', 'PUT']);

        $this->assertSame($name, $route->getName());
        $this->assertSame($pattern, $route->getPattern());
        $this->assertSame(DummyHandler::class, $route->getHandler());
        $this->assertSame($methods, $route->getMethods());

        $this->assertTrue($route->isAllowedMethod('GET'));
        $this->assertFalse($route->isAllowedMethod('POST'));
        $this->assertTrue($route->isAllowedMethod('PUT'));
        $this->assertFalse($route->isAllowedMethod('PATCH'));
        $this->assertFalse($route->isAllowedMethod('DELETE'));
        $this->assertFalse($route->isAllowedMethod('HEAD'));
        $this->assertFalse($route->isAllowedMethod('OPTIONS'));
    }

    public function testAny(): void
    {
        $container = $this->createContainer();
        $app = $container->get(Application::class);
        $route = $app->any($name = 'test', $pattern = '/path', DummyHandler::class);

        $this->assertSame($name, $route->getName());
        $this->assertSame($pattern, $route->getPattern());
        $this->assertSame(DummyHandler::class, $route->getHandler());
        $this->assertSame([], $route->getMethods());

        $this->assertTrue($route->isAllowedMethod('GET'));
        $this->assertTrue($route->isAllowedMethod('POST'));
        $this->assertTrue($route->isAllowedMethod('PUT'));
        $this->assertTrue($route->isAllowedMethod('PATCH'));
        $this->assertTrue($route->isAllowedMethod('DELETE'));
        $this->assertTrue($route->isAllowedMethod('HEAD'));
        $this->assertTrue($route->isAllowedMethod('OPTIONS'));
    }

    public function testGet(): void
    {
        $container = $this->createContainer();
        $app = $container->get(Application::class);
        $route = $app->get($name = 'test', $pattern = '/path', DummyHandler::class);

        $this->assertSame($name, $route->getName());
        $this->assertSame($pattern, $route->getPattern());
        $this->assertSame(DummyHandler::class, $route->getHandler());
        $this->assertSame(['GET'], $route->getMethods());

        $this->assertTrue($route->isAllowedMethod('GET'));
        $this->assertFalse($route->isAllowedMethod('POST'));
        $this->assertFalse($route->isAllowedMethod('PUT'));
        $this->assertFalse($route->isAllowedMethod('PATCH'));
        $this->assertFalse($route->isAllowedMethod('DELETE'));
        $this->assertFalse($route->isAllowedMethod('HEAD'));
        $this->assertFalse($route->isAllowedMethod('OPTIONS'));
    }

    public function testPost(): void
    {
        $container = $this->createContainer();
        $app = $container->get(Application::class);
        $route = $app->post($name = 'test', $pattern = '/path', DummyHandler::class);

        $this->assertSame($name, $route->getName());
        $this->assertSame($pattern, $route->getPattern());
        $this->assertSame(DummyHandler::class, $route->getHandler());
        $this->assertSame(['POST'], $route->getMethods());

        $this->assertFalse($route->isAllowedMethod('GET'));
        $this->assertTrue($route->isAllowedMethod('POST'));
        $this->assertFalse($route->isAllowedMethod('PUT'));
        $this->assertFalse($route->isAllowedMethod('PATCH'));
        $this->assertFalse($route->isAllowedMethod('DELETE'));
        $this->assertFalse($route->isAllowedMethod('HEAD'));
        $this->assertFalse($route->isAllowedMethod('OPTIONS'));
    }

    public function testPut(): void
    {
        $container = $this->createContainer();
        $app = $container->get(Application::class);
        $route = $app->put($name = 'test', $pattern = '/path', DummyHandler::class);

        $this->assertSame($name, $route->getName());
        $this->assertSame($pattern, $route->getPattern());
        $this->assertSame(DummyHandler::class, $route->getHandler());
        $this->assertSame(['PUT'], $route->getMethods());

        $this->assertFalse($route->isAllowedMethod('GET'));
        $this->assertFalse($route->isAllowedMethod('POST'));
        $this->assertTrue($route->isAllowedMethod('PUT'));
        $this->assertFalse($route->isAllowedMethod('PATCH'));
        $this->assertFalse($route->isAllowedMethod('DELETE'));
        $this->assertFalse($route->isAllowedMethod('HEAD'));
        $this->assertFalse($route->isAllowedMethod('OPTIONS'));
    }

    public function testPatch(): void
    {
        $container = $this->createContainer();
        $app = $container->get(Application::class);
        $route = $app->patch($name = 'test', $pattern = '/path', DummyHandler::class);

        $this->assertSame($name, $route->getName());
        $this->assertSame($pattern, $route->getPattern());
        $this->assertSame(DummyHandler::class, $route->getHandler());
        $this->assertSame(['PATCH'], $route->getMethods());

        $this->assertFalse($route->isAllowedMethod('GET'));
        $this->assertFalse($route->isAllowedMethod('POST'));
        $this->assertFalse($route->isAllowedMethod('PUT'));
        $this->assertTrue($route->isAllowedMethod('PATCH'));
        $this->assertFalse($route->isAllowedMethod('DELETE'));
        $this->assertFalse($route->isAllowedMethod('HEAD'));
        $this->assertFalse($route->isAllowedMethod('OPTIONS'));
    }

    public function testDelete(): void
    {
        $container = $this->createContainer();
        $app = $container->get(Application::class);
        $route = $app->delete($name = 'test', $pattern = '/path', DummyHandler::class);

        $this->assertSame($name, $route->getName());
        $this->assertSame($pattern, $route->getPattern());
        $this->assertSame(DummyHandler::class, $route->getHandler());
        $this->assertSame(['DELETE'], $route->getMethods());

        $this->assertFalse($route->isAllowedMethod('GET'));
        $this->assertFalse($route->isAllowedMethod('POST'));
        $this->assertFalse($route->isAllowedMethod('PUT'));
        $this->assertFalse($route->isAllowedMethod('PATCH'));
        $this->assertTrue($route->isAllowedMethod('DELETE'));
        $this->assertFalse($route->isAllowedMethod('HEAD'));
        $this->assertFalse($route->isAllowedMethod('OPTIONS'));
    }

    public function testHead(): void
    {
        $container = $this->createContainer();
        $app = $container->get(Application::class);
        $route = $app->head($name = 'test', $pattern = '/path', DummyHandler::class);

        $this->assertSame($name, $route->getName());
        $this->assertSame($pattern, $route->getPattern());
        $this->assertSame(DummyHandler::class, $route->getHandler());
        $this->assertSame(['HEAD'], $route->getMethods());

        $this->assertFalse($route->isAllowedMethod('GET'));
        $this->assertFalse($route->isAllowedMethod('POST'));
        $this->assertFalse($route->isAllowedMethod('PUT'));
        $this->assertFalse($route->isAllowedMethod('PATCH'));
        $this->assertFalse($route->isAllowedMethod('DELETE'));
        $this->assertTrue($route->isAllowedMethod('HEAD'));
        $this->assertFalse($route->isAllowedMethod('OPTIONS'));
    }

    public function testOptions(): void
    {
        $container = $this->createContainer();
        $app = $container->get(Application::class);
        $route = $app->options($name = 'test', $pattern = '/path', DummyHandler::class);

        $this->assertSame($name, $route->getName());
        $this->assertSame($pattern, $route->getPattern());
        $this->assertSame(DummyHandler::class, $route->getHandler());
        $this->assertSame(['OPTIONS'], $route->getMethods());

        $this->assertFalse($route->isAllowedMethod('GET'));
        $this->assertFalse($route->isAllowedMethod('POST'));
        $this->assertFalse($route->isAllowedMethod('PUT'));
        $this->assertFalse($route->isAllowedMethod('PATCH'));
        $this->assertFalse($route->isAllowedMethod('DELETE'));
        $this->assertFalse($route->isAllowedMethod('HEAD'));
        $this->assertTrue($route->isAllowedMethod('OPTIONS'));
    }

    public function testGroup(): void
    {
        $group = [];
        $dash = $underline = null;
        $container = $this->createContainer();
        $app = $container->get(Application::class);

        $get = $app->get('get', '/get', DummyHandler::class);
        $post = $app->post('post', '/post', DummyHandler::class);
        $put = $app->put('put', '/put', DummyHandler::class);
        $patch = $app->patch('patch', '/patch', DummyHandler::class);
        $delete = $app->delete('delete', '/delete', DummyHandler::class);
        $head = $app->head('head', '/head', DummyHandler::class);
        $options = $app->options('options', '/options', DummyHandler::class);

        $app->group('/group-one', static function (RouteCollector $router) use (&$group): void {
            $group['get_one'] = $router->get('get-one', '/get', DummyHandler::class);
            $group['post_one'] = $router->post('post-one', '/post', DummyHandler::class);
            $group['put_one'] = $router->put('put-one', '/put', DummyHandler::class);
            $group['patch_one'] = $router->patch('patch-one', '/patch', DummyHandler::class);
            $group['delete_one'] = $router->delete('delete-one', '/delete', DummyHandler::class);
            $group['head_one'] = $router->head('head-one', '/head', DummyHandler::class);
            $group['options_one'] = $router->options('options-one', '/options', DummyHandler::class);

            $router->group('/group-two', static function (RouteCollector $router) use (&$group): void {
                $group['get_two'] = $router->get('get-two', '/get', DummyHandler::class);
                $group['post_two'] = $router->post('post-two', '/post', DummyHandler::class);
                $group['put_two'] = $router->put('put-two', '/put', DummyHandler::class);
                $group['patch_two'] = $router->patch('patch-two', '/patch', DummyHandler::class);
                $group['delete_two'] = $router->delete('delete-two', '/delete', DummyHandler::class);
                $group['head_two'] = $router->head('head-two', '/head', DummyHandler::class);
                $group['options_two'] = $router->options('options-two', '/options', DummyHandler::class);
            });
        });

        $app->group('/prefix', static function (RouteCollector $router) use (&$dash): void {
            $dash = $router->get('dash-path', '-dash-path', DummyHandler::class);
        });
        $app->group('/prefix_', static function (RouteCollector $router) use (&$underline): void {
            $underline = $router->get('underline_path', 'underline_path', DummyHandler::class);
        });

        $expected = [
            $get->getName() => $get,
            $post->getName() => $post,
            $put->getName() => $put,
            $patch->getName() => $patch,
            $delete->getName() => $delete,
            $head->getName() => $head,
            $options->getName() => $options,
            $group['get_one']->getName() => $group['get_one'],
            $group['post_one']->getName() => $group['post_one'],
            $group['put_one']->getName() => $group['put_one'],
            $group['patch_one']->getName() => $group['patch_one'],
            $group['delete_one']->getName() => $group['delete_one'],
            $group['head_one']->getName() => $group['head_one'],
            $group['options_one']->getName() => $group['options_one'],
            $group['get_two']->getName() => $group['get_two'],
            $group['post_two']->getName() => $group['post_two'],
            $group['put_two']->getName() => $group['put_two'],
            $group['patch_two']->getName() => $group['patch_two'],
            $group['delete_two']->getName() => $group['delete_two'],
            $group['head_two']->getName() => $group['head_two'],
            $group['options_two']->getName() => $group['options_two'],
            $dash->getName() => $dash,
            $underline->getName() => $underline,
        ];

        $this->assertSame($expected, $container->get(RouteCollector::class)->routes()->getAll());
    }

    /**
     * @param ContainerInterface $container
     * @return string
     */
    private function emittedBody(ContainerInterface $container): string
    {
        return trim($container->get(EmitterInterface::class)->getBody());
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array $headers
     * @return ServerRequestInterface
     */
    private function createServerRequest(string $method, string $uri, array $headers = []): ServerRequestInterface
    {
        return (new ServerRequestFactory())->createServerRequest($method, $uri, $headers);
    }

    /**
     * @return callable
     */
    private function createCallableHandler(): callable
    {
        return static fn(): ResponseInterface => new TextResponse('Callable Handler');
    }

    /**
     * @return RequestHandlerInterface
     */
    private function createContentLengthRequestHandler(): RequestHandlerInterface
    {
        return new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new TextResponse($request->getHeaderLine('content-length'));
            }
        };
    }

    /**
     * @param ContainerInterface $container
     * @param HttpException|null $error
     * @return Application
     */
    private function createApplicationWithPipes(ContainerInterface $container, HttpException $error = null): Application
    {
        $app = $container->get(Application::class);
        $app->pipe(ErrorHandlerMiddleware::class);
        $app->pipe(BodyParamsMiddleware::class);
        $app->pipe(ContentLengthMiddleware::class);
        $app->pipe(RouteMatchMiddleware::class);
        if ($error !== null) {
            $app->pipe(new ErrorMiddleware($error));
        }
        $app->pipe(RouteDispatchMiddleware::class);
        return $app;
    }

    /**
     * @param bool $debug
     * @param bool $json
     * @return ContainerInterface
     */
    private function createContainer(bool $debug = true, bool $json = true): ContainerInterface
    {
        return new Container([
            'debug' => $debug,
            Application::class => function (ContainerInterface $container) use ($json) {
                return new Application(
                    $container->get(RouteCollector::class),
                    $container->get(EmitterInterface::class),
                    $container->get(MiddlewarePipelineInterface::class),
                    $container->get(MiddlewareResolverInterface::class),
                    $this->createNotFoundHandler($container, $json),
                );
            },
            EmitterInterface::class => fn() => new DummyEmitter(),
            RouteCollector::class => fn() => new RouteCollector(),
            MiddlewarePipelineInterface::class => fn() => new MiddlewarePipeline(),
            MiddlewareResolverInterface::class => fn(ContainerInterface $c) => new MiddlewareResolver($c),
            ErrorHandlerMiddleware::class => function (ContainerInterface $container) use ($json) {
                return new ErrorHandlerMiddleware($this->createErrorResponseGenerator($container, $json));
            },
            ResponseFactoryInterface::class => fn() => new CustomResponseFactory(),
            TemplateRendererInterface::class => fn() => new TemplateRenderer(),
        ]);
    }

    /**
     * @param ContainerInterface $container
     * @param bool $json
     * @return RequestHandlerInterface
     */
    private function createNotFoundHandler(ContainerInterface $container, bool $json): RequestHandlerInterface
    {
        return $json ? new NotFoundJsonHandler($container->get('debug')) : new NotFoundHandler(
            $container->get(ResponseFactoryInterface::class),
            $container->get(TemplateRendererInterface::class),
            'not-found',
            $container->get('debug')
        );
    }

    /**
     * @param ContainerInterface $container
     * @param bool $json
     * @return ErrorResponseGeneratorInterface
     */
    private function createErrorResponseGenerator(
        ContainerInterface $container,
        bool $json
    ): ErrorResponseGeneratorInterface {
        return $json ? new ErrorJsonResponseGenerator($container->get('debug')) : new ErrorResponseGenerator(
            $container->get(ResponseFactoryInterface::class),
            $container->get(TemplateRendererInterface::class),
            'error',
            $container->get('debug')
        );
    }

    /**
     * @param ServerRequestInterface $request
     * @param bool $debug
     * @return string
     */
    private function notFoundJsonBody(ServerRequestInterface $request, bool $debug): string
    {
        $data = ['name' => 'Error', 'code' => 404, 'message' => 'Not Found'];

        if ($debug) {
            $data['request'] = $this->traits->extractRequestData($request);
        }

        return $this->encode($data);
    }

    /**
     * @param ServerRequestInterface $request
     * @param TemplateRendererInterface $renderer
     * @param bool $debug
     * @return string
     */
    private function notFoundBody(
        ServerRequestInterface $request,
        TemplateRendererInterface $renderer,
        bool $debug
    ): string {
        return $debug ? get_class($renderer->getEngine()) . ':' . get_class($request) : '404 Not Found';
    }

    /**
     * @param ServerRequestInterface $request
     * @param HttpException $error
     * @param bool $debug
     * @return string
     */
    private function errorJsonBody(ServerRequestInterface $request, HttpException $error, bool $debug): string
    {
        $data = ['name' => 'Error', 'code' => $error->getStatusCode(), 'message' => $error->getReasonPhrase()];

        if ($debug) {
            $data['exception'] = $this->traits->extractExceptionData($error);
            $data['request'] = $this->traits->extractRequestData($request);
        }

        return $this->encode($data);
    }

    /**
     * @param ServerRequestInterface $request
     * @param TemplateRendererInterface $renderer
     * @param HttpException $error
     * @param bool $debug
     * @return string
     */
    private function errorBody(
        ServerRequestInterface $request,
        TemplateRendererInterface $renderer,
        HttpException $error,
        bool $debug
    ): string {
        if ($debug) {
            return get_class($renderer->getEngine()) . ':' . Response::class
                . ':' . get_class($error) . ':' . get_class($request)
            ;
        }

        return $error->getStatusCode() . ' ' . $error->getReasonPhrase();
    }

    /**
     * @param mixed $data
     * @return string
     */
    private function encode($data): string
    {
        return json_encode($this->traits->prepareJsonData($data), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
