<?php

namespace FluxErp\Traits\Action;

use Illuminate\Support\Str;

trait HasSharedFileDefaults
{
    public static function icon(): string
    {
        return 'document';
    }

    public static function label(): string
    {
        return __(Str::headline(class_basename(static::class)));
    }

    public static function accepts(?string $mimeType): bool
    {
        return true;
    }

    public static function supportsMultiple(): bool
    {
        return false;
    }
}
