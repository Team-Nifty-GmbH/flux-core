<?php

namespace FluxErp\Models;

use FluxErp\Traits\Filterable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrintJob extends FluxModel
{
    use Filterable;

    protected function casts(): array
    {
        return [
            'is_completed' => 'boolean',
        ];
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    public function printer(): BelongsTo
    {
        return $this->belongsTo(Printer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
