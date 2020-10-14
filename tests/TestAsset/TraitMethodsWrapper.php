<?php

declare(strict_types=1);

namespace HttpSoft\Tests\Basis\TestAsset;

use HttpSoft\Basis\Response\ExtractErrorDataTrait;
use HttpSoft\Basis\Response\PrepareJsonDataTrait;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class TraitMethodsWrapper
{
    use ExtractErrorDataTrait {
        extractErrorStatusCode as private extractErrorStatusCodeInternal;
        extractExceptionData as private extractExceptionDataInternal;
        extractRequestData as private extractRequestDataInternal;
    }
    use PrepareJsonDataTrait {
        prepareJsonData as private prepareJsonDataInternal;
    }

    /**
     * @param Throwable $error
     * @return int
     */
    public function extractErrorStatusCode(Throwable $error): int
    {
        return $this->extractErrorStatusCodeInternal($error);
    }

    /**
     * @param Throwable $error
     * @return array
     */
    public function extractExceptionData(Throwable $error): array
    {
        return $this->extractExceptionDataInternal($error);
    }

    /**
     * @param ServerRequestInterface $request
     * @return array
     */
    public function extractRequestData(ServerRequestInterface $request): array
    {
        return $this->extractRequestDataInternal($request);
    }

    /**
     * @param mixed $data
     * @return mixed
     */
    public function prepareJsonData($data)
    {
        return $this->prepareJsonDataInternal($data);
    }
}
