<?php

namespace FluxErp\DataType;

/**
 * Handle serialization of arrays.
 */
class ArrayHandler implements HandlerInterface
{
    public function getDataType(): string
    {
        return 'array';
    }

    public function canHandleValue(mixed $value): bool
    {
        return is_array($value);
    }

    public function serializeValue(mixed $value): string
    {
        return json_encode($value);
    }

    /**
     * @return mixed|null
     */
    public function unserializeValue(?string $serializedValue): mixed
    {
        if (is_null($serializedValue)) {
            return null;
        }

        return json_decode($serializedValue, true);
    }
}
