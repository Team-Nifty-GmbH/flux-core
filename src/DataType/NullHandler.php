<?php

namespace FluxErp\DataType;

/**
 * Handle serialization of null values.
 */
class NullHandler extends ScalarHandler
{
    protected string $type = 'NULL';

    public function getDataType(): string
    {
        return 'null';
    }
}
