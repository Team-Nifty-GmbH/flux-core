<?php

namespace FluxErp\Support\MediaLibrary;

use FluxErp\Exceptions\FileIsBlocked;
use FluxErp\Models\BlockedFile;
use Spatie\MediaLibrary\MediaCollections\FileAdder as BaseFileAdder;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\RemoteFile;

class FileAdder extends BaseFileAdder
{
    protected function guardAgainstDisallowedFileAdditions(Media $media): void
    {
        parent::guardAgainstDisallowedFileAdditions($media);

        if (
            ! $this->file instanceof RemoteFile
            && is_file($this->pathToFile)
            && resolve_static(BlockedFile::class, 'isBlocked', ['hash' => md5_file($this->pathToFile)])
        ) {
            throw FileIsBlocked::make($media->file_name);
        }
    }
}
