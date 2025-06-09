<?php

namespace FluxErp\Models;

use FluxErp\Models\Pivots\PrinterUser;
use FluxErp\Traits\Filterable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Printer extends FluxModel
{
    use Filterable;

    protected function casts(): array
    {
        return [
            'media_sizes' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function printerUsers(): HasMany
    {
        return $this->hasMany(PrinterUser::class);
    }

    public function printJobs(): HasMany
    {
        return $this->hasMany(PrintJob::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'printer_user')
            ->using(PrinterUser::class);
    }
}
