<?php

namespace FluxErp\Support\MediaLibrary\ImageGenerators;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use ImagickException;
use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\Conversions\ImageGenerators\ImageGenerator;
use Spatie\MediaLibrary\Conversions\ImageGenerators\Pdf as BasePdf;

class Pdf extends ImageGenerator
{
    public function convert(string $file, ?Conversion $conversion = null): ?string
    {
        try {
            return app(BasePdf::class)->convert($file, $conversion);
        } catch (ImagickException $e) {
            Log::warning('Skipped media conversion: pdf could not be rendered', [
                'file' => basename($file),
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function requirementsAreInstalled(): bool
    {
        return app(BasePdf::class)->requirementsAreInstalled();
    }

    public function supportedExtensions(): Collection
    {
        return app(BasePdf::class)->supportedExtensions();
    }

    public function supportedMimeTypes(): Collection
    {
        return app(BasePdf::class)->supportedMimeTypes();
    }
}
