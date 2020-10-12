<?php

declare(strict_types=1);

namespace HttpSoft\Basis\Response;

use HttpSoft\ErrorHandler\ErrorResponseGeneratorInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

use function array_key_exists;
use function get_class;

trait ExtractErrorDataTrait
{
    /**
     * @param Throwable $error
     * @return int
     */
    private function extractErrorStatusCode(Throwable $error): int
    {
        $code = (int) $error->getCode();

        if (!array_key_exists($code, ErrorResponseGeneratorInterface::ERROR_PHRASES)) {
            $code = ErrorResponseGeneratorInterface::STATUS_INTERNAL_SERVER_ERROR;
        }

        return $code;
    }


    /**
     * @param Throwable $error
     * @return array
     */
    private function extractExceptionData(Throwable $error): array
    {
        return [
            'Class' => get_class($error),
            'Code' => $error->getCode(),
            'Message' => $error->getMessage(),
            'File' => $error->getFile(),
            'Line' => $error->getLine(),
            'Trace' => $error->getTrace()
        ];
    }

    /**
     * @param ServerRequestInterface $request
     * @return array
     */
    private function extractRequestData(ServerRequestInterface $request): array
    {
        return [
            'Method' => $request->getMethod(),
            'URI' => (string) $request->getUri(),
            'Script' => $request->getServerParams()['SCRIPT_NAME'] ?? '',
            'Attributes' => $request->getAttributes(),
            'Query' => $request->getQueryParams(),
            'Body' => $request->getParsedBody(),
            'Cookies' => $request->getCookieParams(),
            'Headers' => $request->getHeaders(),
        ];
    }
}
