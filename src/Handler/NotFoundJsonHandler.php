<?php

declare(strict_types=1);

namespace HttpSoft\Basis\Handler;

use HttpSoft\Basis\Response\ExtractErrorDataTrait;
use HttpSoft\Basis\Response\PrepareJsonDataTrait;
use HttpSoft\ErrorHandler\ErrorResponseGeneratorInterface;
use HttpSoft\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class NotFoundJsonHandler implements RequestHandlerInterface
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
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $code = ErrorResponseGeneratorInterface::STATUS_NOT_FOUND;
        $message = ErrorResponseGeneratorInterface::ERROR_PHRASES[$code];
        $data = ['name' => 'Error', 'code' => $code, 'message' => $message];

        if ($this->debug) {
            $data['request'] = $this->extractRequestData($request);
        }

        return new JsonResponse($this->prepareJsonData($data), $code);
    }
}
