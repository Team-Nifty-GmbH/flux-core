<?php

namespace FluxErp\Contracts;

use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

interface HandlesSharedFiles
{
    public static function icon(): string;

    public static function label(): string;

    public static function accepts(?string $mimeType): bool;

    public static function supportsMultiple(): bool;

    /**
     * @param  TemporaryUploadedFile[]  $files
     * @return string|null redirect url after successful handling
     */
    public function handleSharedFiles(array $files): ?string;
}
