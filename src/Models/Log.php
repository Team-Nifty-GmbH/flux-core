<?php

namespace FluxErp\Models;

use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use MassPrunable;

    public function prunable(): void
    {
        static::where('created_at', '<', now()->subDays(config('logging.channels.database.days')));
    }
}
