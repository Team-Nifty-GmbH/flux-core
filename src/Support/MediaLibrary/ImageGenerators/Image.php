<?php

namespace FluxErp\Support\MediaLibrary\ImageGenerators;

use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\Conversions\ImageGenerators\Image as BaseImage;

class Image extends BaseImage
{
    public function convert(string $path, ?Conversion $conversion = null): string
    {
        $size = @getimagesize($path);
        $maxPixels = config('flux.media.max_image_pixels');

        if (
            $size
            && $maxPixels > 0
            && $maxPixels < $size[0] * $size[1]
        ) {
            Log::warning('Skipped media conversion: image exceeds the pixel limit', [
                'file' => basename($path),
                'width' => $size[0],
                'height' => $size[1],
                'max_pixels' => $maxPixels,
            ]);

            return '';
        }

        return parent::convert($path, $conversion);
    }
}
