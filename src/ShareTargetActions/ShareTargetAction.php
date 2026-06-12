<?php

namespace FluxErp\ShareTargetActions;

use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

abstract class ShareTargetAction
{
    abstract public static function icon(): string;

    abstract public static function label(): string;

    /**
     * @param  TemporaryUploadedFile[]  $files
     * @return string|null redirect url after successful handling
     */
    abstract public function handle(array $files): ?string;

    public static function accepts(?string $mimeType): bool
    {
        return true;
    }

    public static function supportsMultiple(): bool
    {
        return false;
    }
}
