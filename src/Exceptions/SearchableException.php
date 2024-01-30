<?php

namespace FluxErp\Exceptions;

use Exception;

final class SearchableException extends Exception
{
    public static function modelNotSearchable(string $model): self
    {
        return new self("Model '{$model}' is not searchable.");
    }
}
