<?php

namespace FluxErp\Support\MediaLibrary\ImageGenerators;

use Illuminate\Support\Facades\Log;
use ImagickException;
use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\Conversions\ImageGenerators\Pdf as BasePdf;

class Pdf extends BasePdf
{
    public function convert(string $file, ?Conversion $conversion = null): string
    {
        try {
            return parent::convert($file, $conversion);
        } catch (ImagickException $e) {
            Log::warning('Skipped media conversion: pdf could not be rendered', [
                'file' => basename($file),
                'error' => $e->getMessage(),
            ]);

            return '';
        }
    }
}
