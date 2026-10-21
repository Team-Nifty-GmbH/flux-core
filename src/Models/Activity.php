<?php

namespace FluxErp\Models;

use FluxErp\Traits\Model\BroadcastsEvents;
use FluxErp\Traits\Model\ResolvesRelationsThroughContainer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Spatie\Activitylog\Models\Activity as BaseActivity;

class Activity extends BaseActivity
{
    use BroadcastsEvents, MassPrunable, ResolvesRelationsThroughContainer;

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    // Public methods
    public function prunable(): Builder
    {
        return static::query()
            ->where(
                static::getCreatedAtColumn(),
                '<',
                now()->subDays(config('activitylog.clean_after_days', 365))
            );
    }
}
