<?php

namespace FluxErp\Models;

use FluxErp\States\Project\ProjectState;
use FluxErp\Traits\Commentable;
use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasAdditionalColumns;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasSerialNumberRange;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SoftDeletes;
use FluxErp\Traits\Trackable;
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
    use BroadcastsEvents, Commentable, Filterable, HasAdditionalColumns, HasFrontendAttributes,
        HasPackageFactory, HasSerialNumberRange, HasStates, HasTags, HasUserModification, HasUuid, SoftDeletes,
        Trackable;

    protected $guarded = [
        'id',
    ];

    protected $casts = [
        'uuid' => 'string',
        'is_done' => 'boolean',
        'state' => ProjectState::class,
    ];

    public array $filtersExact = [
        'id',
        'project_category_template_id',
    ];

    public string $detailRouteName = 'projects.id';

    protected static function booted(): void
    {
        static::creating(function (Project $project) {
            if (! $project->project_number) {
                $project->getSerialNumber('project_number');
            }
        });
    }

    public function children(): HasMany
    {
        return $this->hasMany(Project::class, 'parent_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'parent_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class);
    }

    public function getLabel(): ?string
    {
        return $this->project_name . ' (' . $this->display_name . ')';
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
}
