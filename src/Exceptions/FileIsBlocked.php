<?php

namespace FluxErp\Exceptions;

use Spatie\MediaLibrary\MediaCollections\Exceptions\FileUnacceptableForCollection;

class FileIsBlocked extends FileUnacceptableForCollection
{
    public static function make(string $fileName): static
    {
        return new static(__('The file :file is blocked and cannot be stored.', ['file' => $fileName]));
    }
}
