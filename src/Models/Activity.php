<?php

namespace FluxErp\Models;

use FluxErp\Settings\ActivityLogSettings;
use FluxErp\Traits\Model\BroadcastsEvents;
use FluxErp\Traits\Model\ResolvesRelationsThroughContainer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
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
            return static::query()->whereRaw('1 = 0');
        }

        return static::query()
            ->where(function (Builder $query) use ($conditions): void {
                foreach ($conditions as $condition) {
                    $query->orWhere($condition);
                }
            });
    }

    protected function attributeChanges(): Attribute
    {
        return Attribute::get(fn (mixed $value): ?Collection => $this->withoutHiddenSubjectAttributes($value))
            ->withoutObjectCaching();
    }

    protected function properties(): Attribute
    {
        return Attribute::get(fn (mixed $value): ?Collection => $this->withoutHiddenSubjectAttributes($value))
            ->withoutObjectCaching();
    }

    protected function withoutHiddenSubjectAttributes(mixed $value): ?Collection
    {
        if (is_null($value)) {
            return null;
        }

        $changes = is_string($value) ? json_decode($value, true) : collect($value)->toArray();

        if (! is_array($changes)) {
            return collect();
        }

        $subjectClass = $this->subject_type ? morphed_model($this->subject_type) ?? $this->subject_type : null;
        $hidden = $subjectClass && class_exists($subjectClass) ? app($subjectClass)->getHidden() : [];

        foreach (['attributes', 'old'] as $key) {
            if (is_array($changes[$key] ?? null)) {
                $changes[$key] = Arr::except($changes[$key], $hidden);
            }
        }

        return collect($changes);
    }
}
