<?php

namespace FluxErp\Models;

use FluxErp\Models\Pivots\Calendarable;
use FluxErp\Models\Pivots\Inviteable;
use FluxErp\Support\Collection\CalendarCollection;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasParentChildRelations;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\LogsActivity;
use FluxErp\Traits\ResolvesRelationsThroughContainer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Str;

class Calendar extends FluxModel
{
    use HasPackageFactory, HasParentChildRelations, HasUserModification, LogsActivity, ResolvesRelationsThroughContainer;

    protected $guarded = [
        'id',
    ];

    protected static function booted(): void
    {
        parent::booted();

        static::deleting(function ($calendar) {
            $calendar->calendarEvents()->delete();
        });
    }

    protected function casts(): array
    {
        return [
            'custom_properties' => 'array',
            'has_notifications' => 'boolean',
            'has_repeatable_events' => 'boolean',
            'is_editable' => 'boolean',
            'is_public' => 'boolean',
        ];
    }

    public function calendarables(): HasMany
    {
        return $this->hasMany(Calendarable::class);
    }

    public function calendarEvents(): HasMany
    {
        return $this->hasMany(CalendarEvent::class);
    }

    public function invitesCalendarEvents()
    {
        return $this->hasManyThrough(
            CalendarEvent::class,
            Inviteable::class,
            'model_calendar_id',
            'id',
            'id',
            'calendar_event_id');
    }

    public function users(): MorphToMany
    {
        return $this->morphedByMany(User::class, 'calendarable', 'calendarables');
    }

    public function newCollection(array $models = []): Collection
    {
        return app(CalendarCollection::class, ['items' => $models]);
    }

    public function toCalendarObject(array $attributes = []): array
    {
        return array_merge(
            [
                'id' => $this->id,
                'parentId' => $this->parent_id,
                'modelType' => $this->model_type,
                'name' => $this->name,
                'color' => $this->color,
                'customProperties' => $this->custom_properties ?? [],
                'resourceEditable' => $this->is_editable ?? true,
                'hasRepeatableEvents' => $this->has_repeatable_events ?? true,
                'isPublic' => $this->is_public ?? false,
                'isShared' => $this->calendarables_count > 1,
            ],
            $attributes
        );
    }

    public function fromCalendarObject(array $calendar): static
    {
        $mappedArray = [];

        foreach ($calendar as $key => $value) {
            $mappedArray[Str::snake($key)] = $value;
        }

        $this->fill($mappedArray);

        return $this;
    }
}
