<?php

namespace FluxErp\Models;

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

    public function prunable(): void
    {
        static::where('created_at', '<', now()->subDays(config('logging.channels.database.days')));
    }
}
