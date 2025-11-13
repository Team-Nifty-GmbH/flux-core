<?php

namespace FluxErp\Models;

use Carbon\Carbon;
use FluxErp\Actions\Task\UpdateTask;
use FluxErp\Casts\Money;
use FluxErp\Casts\TimeDuration;
use FluxErp\Contracts\Calendarable;
use FluxErp\Contracts\IsSubscribable;
use FluxErp\Contracts\Targetable;
use FluxErp\Models\Pivots\TaskUser;
use FluxErp\States\Task\TaskState;
use FluxErp\Support\Scout\ScoutCustomize;
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

class Task extends FluxModel implements Calendarable, HasMedia, InteractsWithDataTables, IsSubscribable, Targetable
{
    use Categorizable, Commentable, Filterable, HasAdditionalColumns, HasFrontendAttributes, HasPackageFactory,
        HasStates, HasTags, HasUserModification, HasUuid, InteractsWithMedia, LogsActivity, SoftDeletes, Trackable;
    use Searchable {
        Searchable::scoutIndexSettings as baseScoutIndexSettings;
    }

    protected ?string $detailRouteName = 'tasks.id';

    public static function aggregateColumns(string $type): array
    {
        return match ($type) {
            'count' => ['id'],
            'avg', 'sum' => [
                'time_budget',
                'budget',
                'total_cost',
            ],
            default => [],
        };
    }

    public static function aggregateTypes(): array
    {
        return [
            'count',
            'avg',
            'sum',
        ];
    }

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

    public static function ownerColumns(): array
    {
        return [
            'responsible_user_id',
            'created_by',
            'updated_by',
        ];
    }

    public static function scoutIndexSettings(): ?array
    {
        return static::baseScoutIndexSettings() ?? [
            'filterableAttributes' => [
                'project_id',
                'state',
            ],
            'sortableAttributes' => ['*'],
        ];
    }

    public static function timeframeColumns(): array
    {
        return [
            'start_date',
            'due_date',
            'created_at',
            'updated_at',
        ];
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

            if ($task->start_date) {
                $newStartDatetime = $task->start_time
                    ? $task->start_date->copy()->setTimeFromTimeString($task->start_time)
                    : $task->start_date->copy()->startOfDay();

                if ($task->start_datetime?->timestamp !== $newStartDatetime->timestamp) {
                    $task->start_reminder_sent_at = null;
                }

                $task->start_datetime = $newStartDatetime;
            } else {
                if ($task->start_datetime !== null) {
                    $task->start_reminder_sent_at = null;
                }

                $task->start_time = null;
                $task->start_datetime = null;
            }

            if ($task->due_date) {
                $newDueDatetime = $task->due_time
                    ? $task->due_date->copy()->setTimeFromTimeString($task->due_time)
                    : $task->due_date->copy()->endOfDay();

                if ($task->due_datetime?->timestamp !== $newDueDatetime->timestamp) {
                    $task->due_reminder_sent_at = null;
                }

                $task->due_datetime = $newDueDatetime;
            } else {
                if ($task->due_datetime !== null) {
                    $task->due_reminder_sent_at = null;
                }

                $task->due_time = null;
                $task->due_datetime = null;
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
            'start_datetime' => 'datetime',
            'start_reminder_sent_at' => 'datetime',
            'due_date' => 'date:Y-m-d',
            'due_datetime' => 'datetime',
            'due_reminder_sent_at' => 'datetime',
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
        Carbon|string $start,
        Carbon|string $end,
        ?array $info = null
    ): void {
        $builder->where(function (Builder $query) use ($start, $end): void {
            $query
                ->whereBetween('start_date', [$start, $end])
                ->orWhereBetween('due_date', [$start, $end])
                ->orWhere(function (Builder $query) use ($start, $end): void {
                    $query->where('start_date', '<=', $end)
                        ->where('due_date', '>=', $start);
                })
                ->orWhere(function (Builder $query) use ($start, $end): void {
                    $query->whereNull('start_date')
                        ->whereNull('due_date')
                        ->whereBetween('created_at', [$start, $end]);
                });
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
                'modelUrl' => $this->getUrl(),
                'modelLabel' => $this->getLabel(),
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
        return ScoutCustomize::make($this)
            ->with('project:id,project_number,name')
            ->toSearchableArray();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_user')->using(TaskUser::class);
    }
}
