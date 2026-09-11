<?php

namespace FluxErp\Models;

use Carbon\Carbon;
use FluxErp\Actions\FluxAction;
use FluxErp\Actions\ResourceBooking\CreateResourceBooking;
use FluxErp\Actions\ResourceBooking\DeleteResourceBooking;
use FluxErp\Actions\ResourceBooking\UpdateResourceBooking;
use FluxErp\Contracts\Calendarable;
use FluxErp\Traits\Model\Filterable;
use FluxErp\Traits\Model\HasPackageFactory;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\HasUuid;
use FluxErp\Traits\Model\LogsActivity;
use FluxErp\Traits\Model\SoftDeletes;
use FluxErp\Traits\Scout\Searchable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;
use TeamNiftyGmbH\DataTable\Contracts\InteractsWithDataTables;

class ResourceBooking extends FluxModel implements Calendarable, InteractsWithDataTables
{
    use Filterable, HasPackageFactory, HasUserModification, HasUuid, LogsActivity, Searchable, SoftDeletes;

    public static string $iconName = 'calendar-days';

    public static function fromCalendarEvent(array $event, string $action = 'update'): FluxAction
    {
        return match ($action) {
            'delete' => DeleteResourceBooking::make(['id' => data_get($event, 'id')]),
            'create' => CreateResourceBooking::make($event),
            default => UpdateResourceBooking::make([
                'id' => data_get($event, 'id'),
                'start' => data_get($event, 'start'),
                'end' => data_get($event, 'end'),
            ]),
        };
    }

    public static function toCalendar(): array
    {
        return [
            'id' => Str::of(static::class)->replace('\\', '.')->toString(),
            'modelType' => morph_alias(static::class),
            'name' => __('Resource Bookings'),
            'color' => '#3b82f6',
            'resourceEditable' => false,
            'hasRepeatableEvents' => false,
            'isPublic' => false,
            'isShared' => false,
            'permission' => 'owner',
            'group' => 'other',
            'isVirtual' => true,
        ];
    }

    protected function casts(): array
    {
        return [
            'start' => 'datetime',
            'end' => 'datetime',
        ];
    }

    public function assignable(): MorphTo
    {
        return $this->morphTo('assignable');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }

    public function getAvatarUrl(): ?string
    {
        return null;
    }

    public function getDescription(): ?string
    {
        return $this->start?->toDateTimeString() . ' – ' . $this->end?->toDateTimeString();
    }

    public function getLabel(): ?string
    {
        return $this->resource?->name;
    }

    public function getUrl(): ?string
    {
        return $this->resource?->getUrl();
    }

    public function toCalendarEvent(?array $info = null): array
    {
        return [
            'id' => $this->getKey(),
            'calendar_type' => $this->getMorphClass(),
            'title' => $this->resource?->name,
            'start' => $this->start?->toDateTimeString(),
            'end' => $this->end?->toDateTimeString(),
            'description' => $this->description,
            'extendedProps' => [
                'modelUrl' => $this->getUrl(),
                'modelLabel' => $this->resource?->name,
            ],
            'allDay' => false,
            'is_editable' => true,
            'is_public' => false,
            'is_repeatable' => false,
        ];
    }

    public function scopeInTimeframe(
        Builder $builder,
        Carbon|string $start,
        Carbon|string $end,
        ?array $info = null
    ): void {
        $builder->with('resource:id,name');
        $builder->where(function (Builder $query) use ($start, $end): void {
            $query
                ->whereBetween('start', [$start, $end])
                ->orWhereBetween('end', [$start, $end])
                ->orWhere(function (Builder $query) use ($start, $end): void {
                    $query->where('start', '<=', $end)
                        ->where('end', '>=', $start);
                });
        });
    }
}
