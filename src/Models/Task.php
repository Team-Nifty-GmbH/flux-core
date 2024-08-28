<?php

namespace FluxErp\Models;

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
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\InteractsWithMedia;
use FluxErp\Traits\LogsActivity;
use FluxErp\Traits\Scout\Searchable;
use FluxErp\Traits\SoftDeletes;
use FluxErp\Traits\Trackable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media as MediaLibraryMedia;
use Spatie\ModelStates\HasStates;
use Spatie\Tags\HasTags;
use TeamNiftyGmbH\DataTable\Contracts\InteractsWithDataTables;
use TeamNiftyGmbH\DataTable\Traits\BroadcastsEvents;

class Task extends Model implements Calendarable, HasMedia, InteractsWithDataTables
{
    use BroadcastsEvents, Categorizable, Commentable, Filterable, HasAdditionalColumns, HasFrontendAttributes,
        HasPackageFactory, HasStates, HasTags, HasUserModification, HasUuid, InteractsWithMedia, LogsActivity,
        Searchable, SoftDeletes, Trackable;

    protected $guarded = [
        'id',
    ];

    protected ?string $detailRouteName = 'tasks.id';

    protected static function booted(): void
    {
        static::saving(function (Task $task) {
            if ($task->state::$isEndState) {
                $task->progress = 1;
            }
        });

        static::saved(function (Task $task) {
            $task->project?->calculateProgress();
        });
    }

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'due_date' => 'date',
            'state' => TaskState::class,
            'time_budget' => TimeDuration::class,
            'total_cost' => Money::class,
        ];
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

    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_user');
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

    public function toSearchableArray(): array
    {
        return $this->with('project:id,project_number,name')
            ->whereKey($this->id)
            ->first()
            ?->toArray() ?? [];
    }

    public function getLabel(): ?string
    {
        return $this->id.' - '.$this->name.($this->project ? ' ('.$this->project->name.')' : '');
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getUrl(): ?string
    {
        return $this->detailRoute();
    }

    public function getAvatarUrl(): ?string
    {
        return null;
    }

    public static function toCalendar(): array
    {
        return [
            'id' => Str::uuid()->toString(),
            'model_type' => app(static::class)->getMorphClass(),
            'name' => __('Tasks'),
            'color' => '#813d9c',
            'resourceEditable' => false,
            'hasRepeatableEvents' => false,
            'isPublic' => false,
            'isShared' => false,
            'permission' => 'owner',
            'group' => 'my',
        ];
    }

    public function toCalendarEvent(): array
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
            'extendedProps' => null,
            'allDay' => false,
            'is_editable' => true,
            'is_invited' => null,
            'is_public' => false,
            'is_repeatable' => false,
        ];
    }

    public static function fromCalendarEvent(array $event): Model
    {
        $task = new static;
        $task->forceFill([
            'id' => $event['id'],
            'name' => $event['title'],
            'start_date' => $event['start'],
            'due_date' => $event['end'],
            'description' => $event['description'],
        ]);

        return $task;
    }

    public function costColumn(): string
    {
        return 'total_cost';
    }
}
