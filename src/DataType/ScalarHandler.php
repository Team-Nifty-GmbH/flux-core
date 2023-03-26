<?php

namespace FluxErp\DataType;

/**
 * Handle serialization of scalar values.
 */
abstract class ScalarHandler implements HandlerInterface
{
    /**
     * The name of the scalar data type.
     */
    protected string $type;

    public function getDataType(): string
    {
        return $this->type;
    }

    public function canHandleValue(mixed $value): bool
    {
        return gettype($value) == $this->type;
    }

    public function serializeValue(mixed $value): string
    {
        settype($value, 'string');

        return $value;
    }

    /**
     * @return string|null
     */
    public function unserializeValue(?string $serializedValue)
    {
        if (is_null($serializedValue)) {
            return null;
        }

        settype($serializedValue, $this->type);

        return $serializedValue;
    }
}
