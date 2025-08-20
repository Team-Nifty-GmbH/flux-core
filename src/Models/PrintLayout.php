<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\InteractsWithMedia;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;

class PrintLayout extends FluxModel implements HasMedia
{
    use HasUserModification, InteractsWithMedia;
    public function casts(): array
    {
        return [
            'margin' => 'array',
            'header' => 'array',
            'footer' => 'array',
            'first_page_header' => 'array',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
