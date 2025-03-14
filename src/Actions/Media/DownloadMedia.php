<?php

namespace FluxErp\Actions\Media;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Media;
use FluxErp\Rulesets\Media\DownloadMediaRuleset;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;

class DownloadMedia extends FluxAction
{
    private ?Media $media = null;

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
        $mediaPath = $this->media->getPath();
        $fileName = $this->media->file_name;

        if ($conversion = $this->getData('conversion')) {
            $mediaPath = $this->media->getPath($conversion);
            $fileName .= '_' . $conversion;
        }

        return match (strtolower($this->getData('as'))) {
            'base64' => base64_encode(file_get_contents($mediaPath)),
            'url' => $this->media->getUrl($conversion),
            'path' => $mediaPath,
            default => response()->download($mediaPath, $fileName)
        };
    }

    protected function prepareForValidation(): void
    {
        $this->data['id'] ??= resolve_static(Media::class, 'query')
            ->where('file_name', $this->getData('file_name'))
            ->where('model_type', $this->getData('model_type'))
            ->where('model_id', $this->getData('model_id'))
            ->value('id');
    }

    protected function validateData(): void
    {
        $this->media = resolve_static(Media::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();

        if (
            (
                $this->getData('conversion')
                && ! $this->media->hasGeneratedConversion($this->getData('conversion'))
            )
            || ! $this->media
        ) {
            throw ValidationException::withMessages([
                'media' => 'File not found',
            ])
                ->status(404);
        }

        parent::validateData();

        if (! auth()->check() && $this->media->disk !== 'public') {
            throw UnauthorizedException::forPermissions(['action.' . static::name()]);
        }
    }
}
