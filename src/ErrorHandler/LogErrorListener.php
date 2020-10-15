<?php

declare(strict_types=1);

namespace HttpSoft\Basis\ErrorHandler;

use HttpSoft\Basis\Response\ExtractErrorDataTrait;
use HttpSoft\ErrorHandler\ErrorListenerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Throwable;

final class LogErrorListener implements ErrorListenerInterface
{
    use ExtractErrorDataTrait;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function trigger(Throwable $error, ServerRequestInterface $request): void
    {
        $this->logger->error($error->getMessage(), [
            'exception' => $this->extractExceptionData($error),
            'request' => $this->extractRequestData($request),
        ]);
    }
}
