<?php

namespace FluxErp\Support\Media;

use FluxErp\Models\Media;
use Illuminate\Support\Carbon;

class DocumentRetention
{
    public function __invoke(Media $media): ?Carbon
    {
        $years = $media->documentType?->retention_years;

        return $years && $media->document_date
            ? $media->document_date->copy()->endOfYear()->addYears($years)->startOfDay()
            : null;
    }
}
