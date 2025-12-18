<?php

namespace FluxErp\Actions\Media;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Media;
use FluxErp\Models\MediaFolder;
use FluxErp\Rulesets\Media\DownloadMultipleMediaRuleset;
use FluxErp\Support\MediaLibrary\FluxMediaStream;
use Illuminate\Support\Str;

class DownloadMultipleMedia extends FluxAction
{
    public static function models(): array
    {
        return [Media::class, MediaFolder::class];
    }

    protected static function getReturnResult(): bool
    {
        return parent::getReturnResult() ?? true;
    }

    protected function getRulesets(): string|array
    {
        return DownloadMultipleMediaRuleset::class;
    }

    public function performAction(): FluxMediaStream
    {
        return FluxMediaStream::create(Str::finish($this->getData('file_name'), '.zip'))
            ->when(
                $mediaFolders = $this->getData('media_folders'),
                fn (FluxMediaStream $stream) => $stream->addMediaFolder(
                    resolve_static(
                        MediaFolder::class,
                        $this->getData('with_subfolders') ? 'familyTree' : 'query'
                    )
                        ->whereKey($mediaFolders)
                        ->get()
                ),
                fn (FluxMediaStream $stream) => $stream->addMedia(
                    resolve_static(Media::class, 'query')
                        ->whereKey($this->getData('media'))
                        ->get()
                )
            );
    }

    protected function prepareForValidation(): void
    {
        $this->data['file_name'] ??= 'media';
        $this->data['with_subfolders'] ??= true;
    }

    protected function validateData(): void
    {
        parent::validateData();

        if ($this->getData('with_subfolders')
            && count($mediaFolders = $this->getData('media_folders')) > 1
        ) {
            foreach ($mediaFolders as $id) {
                $ancestors = resolve_static(MediaFolder::class, 'query')
                    ->whereKey($id)
                    ->first()
                    ?->ancestorKeys() ?? [];

                // Remove all but the uppermost folder of the duplicates
                $duplicates = array_intersect(array_merge([$id], $ancestors), $mediaFolders);
                array_pop($duplicates);

                $mediaFolders = array_diff($mediaFolders, $duplicates);
            }

            $this->data['media_folders'] = array_values($mediaFolders);
        }
    }
}
