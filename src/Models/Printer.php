<?php

namespace FluxErp\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Printer extends FluxModel
{
    protected function casts(): array
    {
        return [
            'media_sizes' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function printJobs(): HasMany
    {
        return $this->hasMany(PrintJob::class);
    }
}
