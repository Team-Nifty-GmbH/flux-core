<?php

namespace FluxErp\Models;

use FluxErp\Traits\Categorizable;
use FluxErp\Traits\Commentable;
use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasAdditionalColumns;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SoftDeletes;
use FluxErp\Traits\HasPackageFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use Categorizable, Commentable, Filterable, HasAdditionalColumns, HasPackageFactory, HasUserModification,
        HasUuid, SoftDeletes;

    protected $guarded = [
        'id',
        'uuid',
    ];

    protected $hidden = [
        'uuid',
    ];

    protected $casts = [
        'uuid' => 'string',
        'is_done' => 'boolean',
    ];

    public array $translatable = [
        'project_name',
        'display_name',
    ];

    public array $filtersExact = [
        'id',
        'project_category_template_id',
    ];

    public string $categoryClass = ProjectTask::class;

    public function categoryTemplate(): BelongsTo
    {
        return $this->belongsTo(ProjectCategoryTemplate::class, 'project_category_template_id');
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
}
