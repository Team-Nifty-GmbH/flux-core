<?php

namespace FluxErp\Models;

use Carbon\Carbon;
use FluxErp\Actions\Task\UpdateTask;
use FluxErp\Casts\Money;
use FluxErp\Casts\TimeDuration;
use FluxErp\Contracts\Calendarable;
use FluxErp\States\Task\TaskState;
use FluxErp\Traits\Categorizable;
use FluxErp\Traits\Commentable;
use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasAdditionalColumns;
use FluxErp\Traits\HasFrontendAttributes;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasTags;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\InteractsWithMedia;
use FluxErp\Traits\LogsActivity;
use FluxErp\Traits\Scout\Searchable;
use FluxErp\Traits\SoftDeletes;
use FluxErp\Traits\Trackable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media as MediaLibraryMedia;
use Spatie\ModelStates\HasStates;
use TeamNiftyGmbH\DataTable\Contracts\InteractsWithDataTables;

class Task extends FluxModel implements Calendarable, HasMedia, InteractsWithDataTables
{
    use Categorizable, Commentable, Filterable, HasAdditionalColumns, HasFrontendAttributes,
        HasPackageFactory, HasStates, HasTags, HasUserModification, HasUuid, InteractsWithMedia, LogsActivity,
        Searchable, SoftDeletes, Trackable;

    protected ?string $detailRouteName = 'tasks.id';

    public static function fromCalendarEvent(array $event, string $action = 'update'): UpdateTask
    {
        return UpdateTask::make([
            'id' => data_get($event, 'id'),
            'name' => data_get($event, 'title'),
            'start_date' => data_get($event, 'start'),
            'due_date' => data_get($event, 'end'),
            'description' => data_get($event, 'description'),
        ]);
    }

    public static function toCalendar(): array
    {
        return [
            'id' => Str::of(static::class)->replace('\\', '.')->toString(),
            'modelType' => morph_alias(static::class),
            'name' => __('Tasks'),
            'color' => '#877ae6',
            'resourceEditable' => false,
            'hasRepeatableEvents' => false,
            'isPublic' => false,
            'isShared' => false,
            'permission' => 'owner',
            'group' => 'other',
            'isVirtual' => true,
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Task $task): void {
            if ($task->state::$isEndState) {
                $task->progress = 1;
            }
        });

        static::saved(function (Task $task): void {
            $task->project?->calculateProgress();
        });
    }

    protected function casts(): array
    {
        return [
            'start_date' => 'date:Y-m-d',
            'due_date' => 'date:Y-m-d',
            'state' => TaskState::class,
            'time_budget' => TimeDuration::class,
            'total_cost' => Money::class,
        ];
    }

    public function costColumn(): string
    {
        return 'total_cost';
    }

    public function getAvatarUrl(): ?string
    {
        return null;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getLabel(): ?string
    {
        return $this->id . ' - ' . $this->name . ($this->project ? ' (' . $this->project->name . ')' : '');
    }

    public function getUrl(): ?string
    {
        return $this->detailRoute();
    }

    public function model(): MorphTo
    {
        return $this->morphTo('model');
    }

    public function orderPosition(): BelongsTo
    {
        return $this->belongsTo(OrderPosition::class);
    }

    public function orderPositions(): BelongsToMany
    {
        return $this->belongsToMany(OrderPosition::class, 'order_position_task')
            ->withPivot('amount');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * @throws \Spatie\Image\Exceptions\InvalidManipulation
     */
    public function registerMediaConversions(?MediaLibraryMedia $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(368)
            ->height(232)
            ->optimize()
            ->nonQueued()
            ->performOnCollections('files');
    }

    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function scopeInTimeframe(
        Builder $builder,
        Carbon|string|null $start,
        Carbon|string|null $end,
        ?array $info = null
    ): void {
        $builder->where(function (Builder $query) use ($start, $end): void {
            $query->whereBetween('start_date', [$start, $end])
                ->orWhereBetween('due_date', [$start, $end])
                ->orWhereBetween('created_at', [$start, $end]);
        });
    }

    public function toCalendarEvent(?array $info = null): array
    {
        return [
            'id' => $this->id,
            'calendar_type' => $this->getMorphClass(),
            'title' => $this->name,
            'start' => ($this->start_date ?? $this->created_at)->toDateTimeString(),
            'end' => $this->due_date?->endOfDay()->toDateTimeString(),
            'status' => $this->state::$name,
            'invited' => [],
            'description' => $this->description,
            'extendedProps' => [
                'appendTitle' => $this->state->badge(),
            ],
            'allDay' => false,
            'is_editable' => true,
            'is_invited' => false,
            'is_public' => false,
            'is_repeatable' => false,
        ];
    }

    public function toSearchableArray(): array
    {
        return $this->with('project:id,project_number,name')
            ->whereKey($this->id)
            ->first()
            ?->toArray() ?? [];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_user');
    }
}
