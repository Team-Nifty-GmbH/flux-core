<?php

namespace FluxErp\Actions\BlockedFile;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\BlockedFile;
use FluxErp\Models\Media;
use FluxErp\Rulesets\BlockedFile\BlockFileRuleset;
use Illuminate\Validation\ValidationException;

class BlockFile extends FluxAction
{
    public static function models(): array
    {
        return [BlockedFile::class];
    }

    protected function getRulesets(): string|array
    {
        return BlockFileRuleset::class;
    }

    public function performAction(): BlockedFile
    {
        $media = resolve_static(Media::class, 'query')
            ->whereKey($this->getData('media_id'))
            ->first();

        $blockedFile = resolve_static(BlockedFile::class, 'query')
            ->firstOrCreate(
                ['hash' => md5_file($media->getPath())],
                [
                    'file_name' => $media->file_name,
                    'mime_type' => $media->mime_type,
                    'size' => $media->size,
                ]
            );

        $media->forceDelete();

        return $blockedFile->withoutRelations()->fresh();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $path = resolve_static(Media::class, 'query')
            ->whereKey($this->getData('media_id'))
            ->first()
            ?->getPath();

        if (! $path || ! is_readable($path)) {
            throw ValidationException::withMessages([
                'media_id' => [__('The file could not be read.')],
            ])
                ->errorBag('blockFile');
        }
    }
}
