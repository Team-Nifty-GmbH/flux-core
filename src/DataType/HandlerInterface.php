<?php

namespace FluxErp\DataType;

/**
 * Provides means to serialize and unserialize values of different data types.
 */
interface HandlerInterface
{
    /**
     * Return the identifier for the data type being handled.
     */
    public function getDataType(): string;

    /**
     * Determine if the value is of the correct type for this handler.
     */
    public function canHandleValue(mixed $value): bool;

    /**
     * Convert the value to a string, so that it can be stored in the database.
     */
    public function serializeValue(mixed $value): string;

    /**
     * Convert a serialized string back to its original value.
     *
     * @return mixed
     */
    public function unserializeValue(?string $serializedValue);
}
