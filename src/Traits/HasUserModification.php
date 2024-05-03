<?php

namespace FluxErp\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\MorphMany;
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
                ->with('causer')
                ->first()
                ?->causer,
        );
    }

    public function updatedBy(): Attribute
    {
        return Attribute::get(function () {
            $activity = $this->activityAttributeQuery('updated')
                ->with('causer')
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

            return $activity?->causer ?: $this->createdBy;
        });
    }

    public function deletedBy(): Attribute
    {
        return Attribute::get(
            fn () => $this->activityAttributeQuery('deleted')
                ->with('causer')
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
