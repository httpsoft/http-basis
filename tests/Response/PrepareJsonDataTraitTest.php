<?php

declare(strict_types=1);

namespace HttpSoft\Tests\Basis\Response;

use DateTime;
use DateTimeZone;
use HttpSoft\Basis\Response\PrepareJsonDataTrait;
use JsonSerializable;
use PHPUnit\Framework\TestCase;
use stdClass;

class PrepareJsonDataTraitTest extends TestCase
{
    use PrepareJsonDataTrait;

    public function testPrepareScalarAndNull(): void
    {
        $this->assertSame(1, $this->prepareJsonData(1));
        $this->assertSame(1.1, $this->prepareJsonData(1.1));
        $this->assertSame(null, $this->prepareJsonData(null));
        $this->assertSame(true, $this->prepareJsonData(true));
        $this->assertSame(false, $this->prepareJsonData(false));
        $this->assertSame('string', $this->prepareJsonData('string'));
    }

    public function testPrepareSimpleArray(): void
    {
        $this->assertSame([1, 2], $this->prepareJsonData([1, 2]));
        $this->assertSame(['a' => 1, 'b' => 2], $this->prepareJsonData(['a' => 1, 'b' => 2]));
    }

    public function testPrepareSimpleObject(): void
    {
        $data = new stdClass();
        $data->a = 1;
        $data->b = 2;
        $this->assertSame(['a' => 1, 'b' => 2], $this->prepareJsonData($data));
    }

    public function testPrepareEmpty(): void
    {
        $data = new stdClass();
        $this->assertSame([], $this->prepareJsonData([]));
        $this->assertEquals($data, $this->prepareJsonData($data));
        $this->assertNotSame($data, $this->prepareJsonData($data));
        $this->assertInstanceOf(stdClass::class, $this->prepareJsonData($data));
    }

    public function testPrepareJsonSerializable(): void
    {
        $this->assertSame(
            ['a' => 1, 'b' => 'string'],
            $this->prepareJsonData(new class () implements JsonSerializable {
                public function jsonSerialize(): array
                {
                    return ['a' => 1, 'b' => 'string'];
                }
            }),
        );
    }

    public function testPrepareDateTime(): void
    {
        $this->assertSame(
            [
                'date' => '2021-08-08 00:00:00.000000',
                'timezone_type' => 3,
                'timezone' => 'UTC',
            ],
            $this->prepareJsonData(new DateTime('August 8, 2021', new DateTimeZone('UTC'))),
        );
    }
}
