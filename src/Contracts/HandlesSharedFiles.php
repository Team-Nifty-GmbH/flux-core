<?php

namespace FluxErp\Contracts;

use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * Marks a FluxAction as available as a target for files shared into the app.
 *
 * Implementing actions are discovered through the ActionManager, so they are
 * registered like any other action. Default implementations for the metadata
 * methods are provided by {@see \FluxErp\Traits\Action\HasSharedFileDefaults}.
 */
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
