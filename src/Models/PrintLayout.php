<?php

namespace FluxErp\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrintLayout extends FluxModel
{
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
