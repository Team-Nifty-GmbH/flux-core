<?php

namespace FluxErp\Actions\Media;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Media;
use FluxErp\Rulesets\Media\DownloadMediaRuleset;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;

class DownloadMedia extends FluxAction
{
    public static function models(): array
    {
        return [Media::class];
    }

    protected function getRulesets(): string|array
    {
        return DownloadMediaRuleset::class;
    }

    public function performAction(): mixed
    {
        /** @var Media $media */
        $media = resolve_static(Media::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();

        $mediaPath = $media->getPath();
        $fileName = $media->file_name;

        if ($conversion = $this->getData('conversion')) {
            $mediaPath = $media->getPath($conversion);
            $fileName = $conversion . '_' . $fileName;
        }

        return match (strtolower($this->getData('as'))) {
            'base64' => base64_encode(file_get_contents($mediaPath)),
            'url' => $media->getUrl($conversion ?? ''),
            'path' => $mediaPath,
            default => response()->download($mediaPath, $fileName)
        };
    }

    protected function validateData(): void
    {
        $media = resolve_static(Media::class, 'query')
            ->when(
                $this->getData('id'),
                fn (Builder $query) => $query->whereKey($this->getData('id')),
                fn (Builder $query) => $query->where('file_name', $this->getData('file_name'))
                    ->where('model_type', $this->getData('model_type'))
                    ->where('model_id', $this->getData('model_id'))
            )
            ->first();

        if (
            ! $media
            || (
                $this->getData('conversion')
                && ! $media?->hasGeneratedConversion($this->getData('conversion'))
            )
        ) {
            throw ValidationException::withMessages([
                'media' => 'File not found',
            ])
                ->status(404);
        }

        if (! auth()->check() && $media->disk !== 'public') {
            throw UnauthorizedException::forPermissions(['action.' . static::name()]);
        }

        parent::validateData();

        $this->data['id'] ??= $media?->getKey();
    }
}
