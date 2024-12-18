<?php

namespace FluxErp\Models;

use FluxErp\Traits\BroadcastsEvents;
use Illuminate\Database\Eloquent\MassPrunable;

class Log extends FluxModel
{
    use BroadcastsEvents, MassPrunable;

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'is_done' => 'boolean',
        ];
    }

    public function prunable(): void
    {
        static::where('created_at', '<', now()->subDays(config('logging.channels.database.days')));
    }
}
