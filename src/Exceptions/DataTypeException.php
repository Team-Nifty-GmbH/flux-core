<?php

namespace FluxErp\Exceptions;

use Exception;

/**
 * Data Type registry exception.
 */
final class DataTypeException extends Exception
{
    public static function handlerNotFound(string $type): self
    {
        return new self("Meta handler not found for type identifier '{$type}'");
    }

    public static function handlerNotFoundForValue($value): self
    {
        $type = is_object($value) ? get_class($value) : gettype($value);

        return new self("Meta handler not found for value of type '{$type}'");
    }
}
