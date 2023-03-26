<?php

namespace FluxErp\DataType;

/**
 * Handle serialization of booleans.
 */
class BooleanHandler extends ScalarHandler
{
    protected string $type = 'boolean';

    public function serializeValue(mixed $value): string
    {
        return $value ? '1' : '0';
    }
}
