<?php

declare(strict_types=1);

namespace HttpSoft\Basis\Response;

use DateTimeInterface;
use JsonSerializable;
use stdClass;

use function is_array;
use function is_object;

trait PrepareJsonDataTrait
{
    /**
     * Pre-processes the data before sending it to `json_encode()`.
     *
     * @param mixed $data data to be pre-processed.
     * @return mixed pre-processed data.
     * @psalm-suppress MixedArrayOffset
     * @psalm-suppress MixedAssignment
     */
    private function prepareJsonData($data)
    {
        if (is_object($data)) {
            if ($data instanceof JsonSerializable) {
                return $this->prepareJsonData($data->jsonSerialize());
            }

            if ($data instanceof DateTimeInterface) {
                return $this->prepareJsonData((array) $data);
            }

            $object = $data;
            $data = [];

            foreach ($object as $name => $value) {
                $data[$name] = $value;
            }

            if ($data === []) {
                return new stdClass();
            }
        }

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (is_array($value) || is_object($value)) {
                    $data[$key] = $this->prepareJsonData($value);
                }
            }
        }

        return $data;
    }
}
