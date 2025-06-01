<?php

namespace FluxErp\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;

class FailedJob extends FluxModel
{
    use MassPrunable;

    protected $guarded = [
        'id',
        'uuid',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'failed_at' => 'datetime',
        ];
    }

    public function prunable(): Builder
    {
        return static::query()
            ->where(
                'failed_at',
                '<',
                now()->subDays(30)
            );
    }
}
