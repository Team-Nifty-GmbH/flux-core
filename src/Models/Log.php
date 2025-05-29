<?php

namespace FluxErp\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;

class Log extends FluxModel
{
    use MassPrunable;

    protected function casts(): array
    {
        return [
            'is_done' => 'boolean',
        ];
    }

    public function prunable(): Builder
    {
        return static::query()
            ->where(
                static::getCreatedAtColumn(),
                '<',
                now()->subDays(config('logging.channels.database.days') ?? 1)
            );
    }
}
