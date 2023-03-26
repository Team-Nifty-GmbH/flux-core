<?php

namespace FluxErp\DataType;

/**
 * Handle serialization of floats.
 */
class FloatHandler extends ScalarHandler
{
    protected string $type = 'double';

    public function getDataType(): string
    {
        return 'float';
    }
}
