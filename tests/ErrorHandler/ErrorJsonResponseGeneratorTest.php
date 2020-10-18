<?php

declare(strict_types=1);

namespace HttpSoft\Tests\Basis\ErrorHandler;

use HttpSoft\Basis\ErrorHandler\ErrorJsonResponseGenerator;
use HttpSoft\Basis\Exception\InternalServerErrorHttpException;
use HttpSoft\Message\ServerRequestFactory;
use HttpSoft\Response\JsonResponse;
use HttpSoft\Tests\Basis\TestAsset\TraitMethodsWrapper;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

use function json_encode;

class ErrorJsonResponseGeneratorTest extends TestCase
{
    /**
     * @var ServerRequestInterface
     */
    private ServerRequestInterface $request;

    /**
     * @var TraitMethodsWrapper
     */
    private TraitMethodsWrapper $traits;

    public function setUp(): void
    {
        $this->request = (new ServerRequestFactory())->createServerRequest(
            'POST',
            'http://example.com',
            ['name' => 'value']
        );
        $this->traits = new TraitMethodsWrapper();
    }

    public function testGenerateWithoutDebugMode(): void
    {
        $generator = new ErrorJsonResponseGenerator(false);
        $response = $generator->generate($exception = new InternalServerErrorHttpException(), $this->request);
        $this->assertInstanceOf(JsonResponse::class, $response);

        $expectedContent = $this->encode([
            'name' => 'Error',
            'code' => $exception->getStatusCode(),
            'message' => $exception->getReasonPhrase(),
        ]);
        $this->assertSame($expectedContent, (string) $response->getBody());
    }

    public function testGenerateWithDebugMode(): void
    {
        $generator = new ErrorJsonResponseGenerator(true);
        $response = $generator->generate($exception = new InternalServerErrorHttpException(), $this->request);
        $this->assertInstanceOf(JsonResponse::class, $response);

        $expectedContent = $this->encode([
            'name' => 'Error',
            'code' => $exception->getStatusCode(),
            'message' => $exception->getReasonPhrase(),
            'exception' => $this->traits->extractExceptionData($exception),
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
