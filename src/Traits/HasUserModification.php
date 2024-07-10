<?php

namespace FluxErp\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

trait HasUserModification
{
    use LogsActivity;

    public function getActivityLogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('model_events')
            ->logAll()
            ->dontLogIfAttributesChangedOnly(['created_at', 'updated_at'])
            ->dontSubmitEmptyLogs()
            ->logExcept(['created_at', 'updated_at', 'deleted_at'])
            ->logOnlyDirty();
    }

    public function initializeHasUserModification(): void
    {
        $this->mergeGuarded([
            'created_at',
            'updated_at',
            'deleted_at',
        ]);
    }

    public function createdBy(): Attribute
    {
        return Attribute::get(
            fn () => $this->activityAttributeQuery('created')
                ->select(['id', 'causer_type', 'causer_id'])
                ->with([
                    'causer' => fn (MorphTo $query) => $query->withoutGlobalScopes()
                        ->withTrashed()
                        ->select(['id', 'name']),
                ])
                ->first()
                ?->causer,
        );
    }

    public function updatedBy(): Attribute
    {
        return Attribute::get(function () {
            $activity = $this->activityAttributeQuery('updated')
                ->select(['id', 'causer_type', 'causer_id'])
                ->with([
                    'causer' => fn (MorphTo $query) => $query->withoutGlobalScopes()
                        ->withTrashed()
                        ->select(['id', 'name']),
                ])
                ->orderBy('id', 'desc')
                ->first();

            if ($activity?->causer_id === $this->id
                && $activity?->causer_type === $this->getMorphClass()
            ) {
                return [
                    'causer_type' => $activity->causer_type,
                    'causer_id' => $activity->causer_id,
                ];
            }

            return $activity?->causer ?? $this->created_by;
        });
    }

    public function deletedBy(): Attribute
    {
        return Attribute::get(
            fn () => $this->activityAttributeQuery('deleted')
                ->select(['id', 'causer_type', 'causer_id'])
                ->with([
                    'causer' => fn (MorphTo $query) => $query->withoutGlobalScopes()
                        ->withTrashed()
                        ->select(['id', 'name']),
                ])
                ->orderBy('id', 'desc')
                ->first()
                ?->causer
        );
    }

    private function activityAttributeQuery(?string $event = null): MorphMany
    {
        $query = $this->activities()
            ->where('log_name', 'model_events');

        if ($event) {
            $query->where('event', $event);
        }

        return $query;
    }
}
