<?php

declare(strict_types=1);

namespace HttpSoft\Tests\Basis\ErrorHandler;

use Exception;
use HttpSoft\Basis\ErrorHandler\LogErrorListener;
use HttpSoft\Message\ServerRequestFactory;
use HttpSoft\Tests\Basis\TestAsset\TraitMethodsWrapper;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class LogErrorListenerTest extends TestCase
{
    public function testTrigger()
    {
        $logger = $this->createLogger();
        $listener = new LogErrorListener($logger);
        $exception = new Exception($message = 'Message', 1);
        $request = (new ServerRequestFactory())->createServerRequest(
            'POST',
            'http://example.com',
            ['name' => 'value']
        );
        $listener->trigger($exception, $request);

        $traits = new TraitMethodsWrapper();
        $context = [
            'exception' => $traits->extractExceptionData($exception),
            'request' => $traits->extractRequestData($request),
        ];

        $this->assertSame($message, $logger->message);
        $this->assertSame($context, $logger->context);
    }

    private function createLogger(): LoggerInterface
    {
        return new class implements LoggerInterface {
            public string $message = '';
            public array $context = [];

            public function error($message, array $context = [])
            {
                $this->message = $message;
                $this->context = $context;
            }

            public function emergency($message, array $context = [])
            {
            }

            public function alert($message, array $context = [])
            {
            }

            public function critical($message, array $context = [])
            {
            }

            public function warning($message, array $context = [])
            {
            }

            public function notice($message, array $context = [])
            {
            }

            public function info($message, array $context = [])
            {
            }

            public function debug($message, array $context = [])
            {
            }

            public function log($level, $message, array $context = [])
            {
            }
        };
    }
}
