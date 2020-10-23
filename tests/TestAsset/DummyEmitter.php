<?php

declare(strict_types=1);

namespace HttpSoft\Tests\Basis\TestAsset;

use HttpSoft\Emitter\EmitterInterface;
use Psr\Http\Message\ResponseInterface;

class DummyEmitter implements EmitterInterface
{
    /**
     * @var string
     */
    private string $body = '';

    /**
     * {@inheritDoc}
     */
    public function emit(ResponseInterface $response, bool $withoutBody = false): void
    {
        $this->body = (string) $response->getBody();
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }
}
