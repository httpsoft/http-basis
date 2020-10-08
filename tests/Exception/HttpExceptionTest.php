<?php

declare(strict_types=1);

namespace HttpSoft\Tests\Basis\Exception;

use Exception;
use HttpSoft\Basis\Exception\HttpException;
use HttpSoft\ErrorHandler\ErrorResponseGeneratorInterface;
use PHPUnit\Framework\TestCase;

class HttpExceptionTest extends TestCase
{
    public function testHttpExceptionOnlyWithCode()
    {
        $statusCode = ErrorResponseGeneratorInterface::STATUS_NOT_FOUND;
        $reasonPhrase = ErrorResponseGeneratorInterface::ERROR_PHRASES[$statusCode];
        $exception = new HttpException($statusCode);

        $this->assertSame($statusCode, $exception->getStatusCode());
        $this->assertSame($reasonPhrase, $exception->getReasonPhrase());
        $this->assertSame($statusCode . ' ' . $reasonPhrase, $exception->getTitle());

        $this->assertSame($statusCode, $exception->getCode());
        $this->assertSame($reasonPhrase, $exception->getMessage());
        $this->assertSame(null, $exception->getPrevious());
    }

    public function testHttpExceptionWithReasonPhrase()
    {
        $statusCode = 599;
        $reasonPhrase = 'Custom Reason Phrase';
        $exception = new HttpException($statusCode, $reasonPhrase);

        $this->assertSame($statusCode, $exception->getStatusCode());
        $this->assertSame($reasonPhrase, $exception->getReasonPhrase());
        $this->assertSame($statusCode . ' ' . $reasonPhrase, $exception->getTitle());

        $this->assertSame($statusCode, $exception->getCode());
        $this->assertSame($reasonPhrase, $exception->getMessage());
        $this->assertSame(null, $exception->getPrevious());
    }

    public function testHttpExceptionWithUnassignedStatusCodeAndEmptyReasonPhrase()
    {
        $statusCode = 599;
        $reasonPhrase = '';
        $exception = new HttpException($statusCode, $reasonPhrase);

        $this->assertSame($statusCode, $exception->getStatusCode());
        $this->assertSame($reasonPhrase, $exception->getReasonPhrase());
        $this->assertSame((string) $statusCode, $exception->getTitle());

        $this->assertSame($statusCode, $exception->getCode());
        $this->assertSame($reasonPhrase, $exception->getMessage());
        $this->assertSame(null, $exception->getPrevious());
    }

    public function testHttpExceptionWithoutReasonPhraseAndWithPrevious()
    {
        $previousCode = 1;
        $previousMessage = 'Previous Message';
        $previous = new Exception($previousMessage, $previousCode);

        $statusCode = ErrorResponseGeneratorInterface::STATUS_NOT_FOUND;
        $reasonPhrase = ErrorResponseGeneratorInterface::ERROR_PHRASES[$statusCode];
        $exception = new HttpException($statusCode, null, $previous);

        $this->assertSame($statusCode, $exception->getStatusCode());
        $this->assertSame($reasonPhrase, $exception->getReasonPhrase());
        $this->assertSame($statusCode . ' ' . $reasonPhrase, $exception->getTitle());

        $this->assertSame($statusCode, $exception->getCode());
        $this->assertSame($reasonPhrase, $exception->getMessage());
        $this->assertSame($previous, $exception->getPrevious());

        $this->assertSame($previousCode, $previous->getCode());
        $this->assertSame($previousMessage, $previous->getMessage());
        $this->assertSame(null, $previous->getPrevious());
    }

    public function testHttpExceptionWithReasonPhraseAndWithPrevious()
    {
        $previousCode = 1;
        $previousMessage = 'Previous Message';
        $previous = new Exception($previousMessage, $previousCode);

        $statusCode = 599;
        $reasonPhrase = 'Custom Reason Phrase';
        $exception = new HttpException($statusCode, $reasonPhrase, $previous);

        $this->assertSame($statusCode, $exception->getStatusCode());
        $this->assertSame($reasonPhrase, $exception->getReasonPhrase());
        $this->assertSame($statusCode . ' ' . $reasonPhrase, $exception->getTitle());

        $this->assertSame($statusCode, $exception->getCode());
        $this->assertSame($reasonPhrase, $exception->getMessage());
        $this->assertSame($previous, $exception->getPrevious());

        $this->assertSame($previousCode, $previous->getCode());
        $this->assertSame($previousMessage, $previous->getMessage());
        $this->assertSame(null, $previous->getPrevious());
    }
}
