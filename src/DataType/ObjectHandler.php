<?php

namespace FluxErp\DataType;

/**
 * Handle serialization of plain objects.
 */
class ObjectHandler implements HandlerInterface
{
    public function canHandleValue(mixed $value): bool
    {
        return is_object($value);
    }

    public function getDataType(): string
    {
        return 'object';
    }

    public function serializeValue(mixed $value): string
    {
        return json_encode($value);
    }

    /**
     * @return mixed|null
     */
    public function unserializeValue(?string $serializedValue)
    {
        if (is_null($serializedValue)) {
            return null;
        }

        return json_decode($serializedValue, false);
    }
}
