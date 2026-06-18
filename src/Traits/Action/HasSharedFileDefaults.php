<?php

namespace FluxErp\Traits\Action;

use Illuminate\Support\Str;

trait HasSharedFileDefaults
{
    public static function icon(): string
    {
        return 'document-arrow-up';
    }

    public static function label(): string
    {
        return __(Str::headline(class_basename(static::class)));
    }

    public static function accepts(?string $mimeType): bool
    {
        return in_array($mimeType, static::acceptedMimeTypes(), true);
    }

    public static function supportsMultiple(): bool
    {
        return true;
    }

    protected static function acceptedMimeTypes(): array
    {
        return [
            'application/pdf',
            'image/jpeg',
            'image/png',
        ];
    }
}
