<?php

namespace FluxErp\Models;

use FluxErp\Settings\ActivityLogSettings;
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
        $settings = app(ActivityLogSettings::class);
        $retentionDays = [
            'created' => $settings->created_retention_days,
            'updated' => $settings->updated_retention_days,
            'deleted' => $settings->deleted_retention_days,
        ];

        $conditions = [];
        foreach (array_filter($retentionDays, fn (?int $days) => $days > 0) as $event => $days) {
            $conditions[] = fn (Builder $query) => $query
                ->where('event', $event)
                ->where(static::getCreatedAtColumn(), '<', now()->subDays($days));
        }

        if ($settings->other_retention_days > 0) {
            $conditions[] = fn (Builder $query) => $query
                ->where(fn (Builder $query) => $query
                    ->whereNotIn('event', array_keys($retentionDays))
                    ->orWhereNull('event')
                )
                ->where(static::getCreatedAtColumn(), '<', now()->subDays($settings->other_retention_days));
        }

        if (! $conditions) {
            return static::query()->whereKey([]);
        }

        return static::query()
            ->where(function (Builder $query) use ($conditions): void {
                foreach ($conditions as $condition) {
                    $query->orWhere($condition);
                }
            });
    }
}
