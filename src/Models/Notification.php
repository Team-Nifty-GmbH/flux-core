<?php

namespace FluxErp\Models;

use FluxErp\Traits\ResolvesRelationsThroughContainer;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Notifications\DatabaseNotification;

class Notification extends DatabaseNotification
{
    use MassPrunable, ResolvesRelationsThroughContainer;

    protected $guarded = [
        'id',
    ];

    public function prunable(): mixed
    {
        return static::where('created_at', '<', now()->subDays(30))->whereNotNull('read_at');
    }
}
