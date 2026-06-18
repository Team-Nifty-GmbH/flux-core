<?php

namespace FluxErp\Traits\Action;

use Illuminate\Support\Str;

/**
 * Default metadata for actions implementing {@see \FluxErp\Contracts\HandlesSharedFiles}.
 *
 * Accepts every mime type and a single file by default; concrete actions
 * override what they need.
 */
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
