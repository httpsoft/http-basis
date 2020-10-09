<?php

declare(strict_types=1);

namespace HttpSoft\Tests\Basis\Exception;

use Exception;
use HttpSoft\Basis\Exception\BadRequestHttpException;
use HttpSoft\ErrorHandler\ErrorResponseGeneratorInterface;
use PHPUnit\Framework\TestCase;

class BadRequestHttpExceptionTest extends TestCase
{
    /**
     * @var int
     */
    private int $statusCode;

    /**
     * @var string
     */
    private string $reasonPhrase;

    public function setUp(): void
    {
        $this->statusCode = ErrorResponseGeneratorInterface::STATUS_BAD_REQUEST;
        $this->reasonPhrase = ErrorResponseGeneratorInterface::ERROR_PHRASES[$this->statusCode];
    }

    public function testBadRequestHttpExceptionWithoutArguments()
    {
        $exception = new BadRequestHttpException();

        $this->assertSame($this->statusCode, $exception->getStatusCode());
        $this->assertSame($this->reasonPhrase, $exception->getReasonPhrase());
        $this->assertSame($this->statusCode . ' ' . $this->reasonPhrase, $exception->getTitle());

        $this->assertSame($this->statusCode, $exception->getCode());
        $this->assertSame($this->reasonPhrase, $exception->getMessage());
        $this->assertSame(null, $exception->getPrevious());
    }

    public function testBadRequestHttpExceptionWithReasonPhrase()
    {
        $reasonPhrase = 'Custom Reason Phrase';
        $exception = new BadRequestHttpException($reasonPhrase);

        $this->assertSame($this->statusCode, $exception->getStatusCode());
        $this->assertSame($reasonPhrase, $exception->getReasonPhrase());
        $this->assertSame($this->statusCode . ' ' . $reasonPhrase, $exception->getTitle());

        $this->assertSame($this->statusCode, $exception->getCode());
        $this->assertSame($reasonPhrase, $exception->getMessage());
        $this->assertSame(null, $exception->getPrevious());
    }

    public function testHttpExceptionWithReasonPhraseAndWithPrevious()
    {
        $previousCode = 1;
        $previousMessage = 'Previous Message';
        $previous = new Exception($previousMessage, $previousCode);
        $exception = new BadRequestHttpException('', $previous);

        $this->assertSame($this->statusCode, $exception->getStatusCode());
        $this->assertSame($this->reasonPhrase, $exception->getReasonPhrase());
        $this->assertSame($this->statusCode . ' ' . $this->reasonPhrase, $exception->getTitle());

        $this->assertSame($this->statusCode, $exception->getCode());
        $this->assertSame($this->reasonPhrase, $exception->getMessage());
        $this->assertSame($previous, $exception->getPrevious());

        $this->assertSame($previousCode, $previous->getCode());
        $this->assertSame($previousMessage, $previous->getMessage());
        $this->assertSame(null, $previous->getPrevious());
    }
}
