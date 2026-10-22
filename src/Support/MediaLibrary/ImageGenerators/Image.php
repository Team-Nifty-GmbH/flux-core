<?php

namespace FluxErp\Support\MediaLibrary\ImageGenerators;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\Conversions\ImageGenerators\Image as BaseImage;
use Spatie\MediaLibrary\Conversions\ImageGenerators\ImageGenerator;

class Image extends ImageGenerator
{
    public function convert(string $file, ?Conversion $conversion = null): ?string
    {
        $size = @getimagesize($file);
        $maxPixels = config('flux.media.max_image_pixels');

        if ($size && $maxPixels > 0 && $maxPixels < $size[0] * $size[1]) {
            Log::warning('Skipped media conversion: image exceeds the pixel limit', [
                'file' => basename($file),
                'width' => $size[0],
                'height' => $size[1],
                'max_pixels' => $maxPixels,
            ]);

            return null;
        }

        return $file;
    }

    public function requirementsAreInstalled(): bool
    {
        return app(BaseImage::class)->requirementsAreInstalled();
    }

    public function supportedExtensions(): Collection
    {
        return app(BaseImage::class)->supportedExtensions();
    }

    public function supportedMimeTypes(): Collection
    {
        return app(BaseImage::class)->supportedMimeTypes();
    }
}
