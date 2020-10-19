<?php

declare(strict_types=1);

namespace HttpSoft\Tests\Basis\Handler;

use HttpSoft\Basis\Handler\NotFoundJsonHandler;
use HttpSoft\ErrorHandler\ErrorResponseGeneratorInterface;
use HttpSoft\Message\ServerRequestFactory;
use HttpSoft\Tests\Basis\TestAsset\TraitMethodsWrapper;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class NotFoundJsonHandlerTest extends TestCase
{
    /**
     * @var ServerRequestInterface
     */
    private ServerRequestInterface $request;

    /**
     * @var TraitMethodsWrapper
     */
    private TraitMethodsWrapper $traits;

    /**
     * @var int
     */
    private int $statusCode = ErrorResponseGeneratorInterface::STATUS_NOT_FOUND;

    public function setUp(): void
    {
        $this->request = (new ServerRequestFactory())->createServerRequest(
            'POST',
            'http://example.com',
            ['name' => 'value']
        );
        $this->traits = new TraitMethodsWrapper();
    }

    public function testHandleWithoutDebugMode(): void
    {
        $handler = new NotFoundJsonHandler(false);
        $response = $handler->handle($this->request);

        $expectedContent = $this->encode([
            'name' => 'Error',
            'code' => $this->statusCode,
            'message' => ErrorResponseGeneratorInterface::ERROR_PHRASES[$this->statusCode],
        ]);
        $this->assertSame($expectedContent, (string) $response->getBody());
    }

    public function testHandleWithDebugMode(): void
    {
        $handler = new NotFoundJsonHandler(true);
        $response = $handler->handle($this->request);

        $expectedContent = $this->encode([
            'name' => 'Error',
            'code' => $this->statusCode,
            'message' => ErrorResponseGeneratorInterface::ERROR_PHRASES[$this->statusCode],
            'request' => $this->traits->extractRequestData($this->request),
        ]);
        $this->assertSame($expectedContent, (string) $response->getBody());
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
