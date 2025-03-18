<?php

namespace FluxErp\Actions\Media;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Media;
use FluxErp\Rulesets\Media\DownloadMultipleMediaRuleset;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\Support\MediaStream;

class DownloadMultipleMedia extends FluxAction
{
    protected static bool $returnResult = true;

    public static function models(): array
    {
        return [Media::class];
    }

    protected function getRulesets(): string|array
    {
        return DownloadMultipleMediaRuleset::class;
    }

    public function performAction(): MediaStream
    {
        return MediaStream::create(Str::finish($this->getData('file_name') ?? 'media', '.zip'))
            ->addMedia(
                resolve_static(Media::class, 'query')
                    ->whereKey($this->getData('ids'))
                    ->get()
            );
    }
}
