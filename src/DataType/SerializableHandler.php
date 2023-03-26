<?php

namespace FluxErp\DataType;

use Serializable;

/**
 * Handle serialization of Serializable objects.
 */
class SerializableHandler implements HandlerInterface
{
    public function getDataType(): string
    {
        return 'serializable';
    }

    public function canHandleValue(mixed $value): bool
    {
        return $value instanceof Serializable;
    }

    public function serializeValue(mixed $value): string
    {
        return serialize($value);
    }

    /**
     * @return mixed|null
     */
    public function unserializeValue(?string $serializedValue)
    {
        if (is_null($serializedValue)) {
            return null;
        }

        return unserialize($serializedValue);
    }
}
