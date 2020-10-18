<?php

declare(strict_types=1);

namespace HttpSoft\Basis\ErrorHandler;

use HttpSoft\Basis\Response\ExtractErrorDataTrait;
use HttpSoft\Basis\Response\PrepareJsonDataTrait;
use HttpSoft\ErrorHandler\ErrorResponseGeneratorInterface;
use HttpSoft\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

final class ErrorJsonResponseGenerator implements ErrorResponseGeneratorInterface
{
    use ExtractErrorDataTrait;
    use PrepareJsonDataTrait;

    /**
     * @var bool
     */
    private bool $debug;

    /**
     * @param bool $debug
     */
    public function __construct(bool $debug = false)
    {
        $this->debug = $debug;
    }

    /**
     * {@inheritDoc}
     */
    public function generate(Throwable $error, ServerRequestInterface $request): ResponseInterface
    {
        $code = $this->extractErrorStatusCode($error);
        $message = ErrorResponseGeneratorInterface::ERROR_PHRASES[$code];
        $data = ['name' => 'Error', 'code' => $code, 'message' => $message];

        if ($this->debug) {
            $data['exception'] = $this->extractExceptionData($error);
            $data['request'] = $this->extractRequestData($request);
        }

        return new JsonResponse($this->prepareJsonData($data), $code);
    }
}
