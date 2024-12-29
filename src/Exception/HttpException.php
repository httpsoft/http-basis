<?php

declare(strict_types=1);

namespace HttpSoft\Basis\Exception;

use Exception;
use HttpSoft\ErrorHandler\ErrorResponseGeneratorInterface;
use Throwable;

class HttpException extends Exception
{
    /**
     * @var int
     */
    private int $statusCode;

    /**
     * @var string
     */
    private string $reasonPhrase;

    /**
     * @param int $statusCode
     * @param string|null $reasonPhrase
     * @param Throwable|null $previous
     */
    public function __construct(int $statusCode, ?string $reasonPhrase = null, ?Throwable $previous = null)
    {
        $reasonPhrase ??= '';

        if ($reasonPhrase === '' && isset(ErrorResponseGeneratorInterface::ERROR_PHRASES[$statusCode])) {
            $reasonPhrase = ErrorResponseGeneratorInterface::ERROR_PHRASES[$statusCode];
        }

        $this->statusCode = $statusCode;
        $this->reasonPhrase = $reasonPhrase;
        parent::__construct($reasonPhrase, $statusCode, $previous);
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return string
     */
    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->statusCode . ($this->reasonPhrase ? ' ' . $this->reasonPhrase : '');
    }
}
