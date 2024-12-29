<?php

declare(strict_types=1);

namespace HttpSoft\Basis\Exception;

use HttpSoft\ErrorHandler\ErrorResponseGeneratorInterface;
use Throwable;

class NotImplementedHttpException extends HttpException
{
    /**
     * @param string|null $reasonPhrase
     * @param Throwable|null $previous
     */
    public function __construct(?string $reasonPhrase = null, ?Throwable $previous = null)
    {
        parent::__construct(ErrorResponseGeneratorInterface::STATUS_NOT_IMPLEMENTED, $reasonPhrase, $previous);
    }
}
