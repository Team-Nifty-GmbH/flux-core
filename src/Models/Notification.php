<?php

namespace FluxErp\Models;

use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Notifications\DatabaseNotification;

class Notification extends DatabaseNotification
{
    use MassPrunable;

    public function prunable(): mixed
    {
        return static::where('created_at', '<', now()->subDays(30))->whereNotNull('read_at');
    }
}
