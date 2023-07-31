<?php

namespace FluxErp\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

trait HasUserModification
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
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
                ->first()
                ?->causer,
        );
    }

    public function updatedBy(): Attribute
    {
        return Attribute::get(
            fn () => $this->activityAttributeQuery('updated')
                ->orderBy('id', 'desc')
                ->first()
                ?->causer ?: $this->createdBy
        );
    }

    public function deletedBy(): Attribute
    {
        return Attribute::get(
            fn () => $this->activityAttributeQuery('deleted')
                ->orderBy('id', 'desc')
                ->first()
                ?->morphTo('causer')
                ->first()
        );
    }

    private function activityAttributeQuery(string $event = null): MorphMany
    {
        $query = $this->activities()
            ->where('log_name', 'model_events');

        if ($event) {
            $query->where('event', $event);
        }

        return $query;
    }
}
