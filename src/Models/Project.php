<?php

namespace FluxErp\Models;

use FluxErp\Casts\TimeDuration;
use FluxErp\States\Project\ProjectState;
use FluxErp\Traits\Commentable;
use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasAdditionalColumns;
use FluxErp\Traits\HasClientAssignment;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasSerialNumberRange;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\LogsActivity;
use FluxErp\Traits\Scout\Searchable;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\ModelStates\HasStates;
use Spatie\Tags\HasTags;
use TeamNiftyGmbH\DataTable\Contracts\InteractsWithDataTables;
use TeamNiftyGmbH\DataTable\Traits\BroadcastsEvents;
use TeamNiftyGmbH\DataTable\Traits\HasFrontendAttributes;

class Project extends Model implements InteractsWithDataTables
{
    use BroadcastsEvents, Commentable, Filterable, HasAdditionalColumns, HasClientAssignment, HasFrontendAttributes,
        HasPackageFactory, HasSerialNumberRange, HasStates, HasTags, HasUserModification, HasUuid, LogsActivity,
        Searchable, SoftDeletes;

    protected $guarded = [
        'id',
    ];

    protected ?string $detailRouteName = 'projects.id';

    protected static function booted(): void
    {
        static::creating(function (Project $project) {
            if (! $project->project_number) {
                $project->getSerialNumber('project_number');
            }
        });
    }

    protected function casts(): array
    {
        return [
            'start_date' => 'date:Y-m-d',
            'end_date' => 'date:Y-m-d',
            'state' => ProjectState::class,
            'time_budget' => TimeDuration::class,
        ];
    }

    public function children(): HasMany
    {
        return $this->hasMany(Project::class, 'parent_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'parent_id');
    }

    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function getLabel(): ?string
    {
        return $this->name . ' (' . $this->project_number . ')';
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

    public function calculateProgress(): void
    {
        $this->progress = bcdiv(
            $this->tasks()->sum('progress'),
            $this->tasks()->count()
        );
        $this->total_cost = $this->tasks()->sum('total_cost');

        $this->save();

        if ($this->order) {
            $this->order->calculateMargin()->save();
        }
    }
}
