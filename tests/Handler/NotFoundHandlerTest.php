<?php

declare(strict_types=1);

namespace HttpSoft\Tests\Basis\Handler;

use HttpSoft\Basis\Handler\NotFoundHandler;
use HttpSoft\Basis\Response\CustomResponseFactory;
use HttpSoft\Basis\TemplateRendererInterface;
use HttpSoft\Message\ServerRequestFactory;
use HttpSoft\Tests\Basis\TestAsset\TemplateRenderer;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;

use function get_class;
use function trim;

class NotFoundHandlerTest extends TestCase
{
    /**
     * @var ServerRequestInterface
     */
    private ServerRequestInterface $request;

    /**
     * @var TemplateRendererInterface
     */
    private TemplateRendererInterface $renderer;

    /**
     * @var ResponseFactoryInterface
     */
    private ResponseFactoryInterface $responseFactory;

    public function setUp(): void
    {
        $this->request = (new ServerRequestFactory())->createServerRequest('GET', 'http://example.com');
        $this->renderer = new TemplateRenderer();
        $this->responseFactory = new CustomResponseFactory();
    }

    public function testHandleWithoutDebugMode(): void
    {
        $handler = new NotFoundHandler($this->responseFactory, $this->renderer, 'not-found', false);
        $response = $handler->handle($this->request);

        $expectedContent = '404 Not Found';
        $this->assertSame($expectedContent, trim((string) $response->getBody()));
    }

    public function testHandleWithDebugMode(): void
    {
        $handler = new NotFoundHandler($this->responseFactory, $this->renderer, 'not-found', true);
        $response = $handler->handle($this->request);

        $expectedContent = get_class($this->renderer->getEngine()) . ':' . get_class($this->request);
        $this->assertSame($expectedContent, trim((string) $response->getBody()));
    }
}
