<?php

declare(strict_types=1);

namespace HttpSoft\Tests\Basis\ErrorHandler;

use HttpSoft\Basis\ErrorHandler\ErrorResponseGenerator;
use HttpSoft\Basis\Exception\InternalServerErrorHttpException;
use HttpSoft\Basis\Response\CustomResponseFactory;
use HttpSoft\Basis\TemplateRendererInterface;
use HttpSoft\Message\ServerRequestFactory;
use HttpSoft\Tests\Basis\TestAsset\TemplateRenderer;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;

use function get_class;
use function trim;

class ErrorResponseGeneratorTest extends TestCase
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

    public function testGenerateWithoutDebugMode(): void
    {
        $generator = new ErrorResponseGenerator($this->responseFactory, $this->renderer, 'error', false);
        $response = $generator->generate($exception = new InternalServerErrorHttpException(), $this->request);

        $expectedContent = $exception->getStatusCode() . ' ' . $exception->getReasonPhrase();
        $this->assertSame($expectedContent, trim((string) $response->getBody()));
    }

    public function testGenerateWithDebugMode(): void
    {
        $generator = new ErrorResponseGenerator($this->responseFactory, $this->renderer, 'error', true);
        $response = $generator->generate(new InternalServerErrorHttpException(), $this->request);

        $expectedContent = get_class($this->renderer->getEngine()) . ':' . get_class($response);
        $expectedContent .= ':' . InternalServerErrorHttpException::class . ':' . get_class($this->request);
        $this->assertSame($expectedContent, trim((string) $response->getBody()));
    }
}
