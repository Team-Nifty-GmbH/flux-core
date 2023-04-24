<?php

namespace FluxErp\Models;

use FluxErp\Traits\Categorizable;
use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasAdditionalColumns;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectCategoryTemplate extends Model
{
    use Categorizable, Filterable, HasAdditionalColumns, HasPackageFactory, HasUserModification, HasUuid;

    protected $guarded = [
        'id',
        'uuid',
    ];

    public $translatable = [
        'name',
    ];

    public string $categoryClass = ProjectTask::class;

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }
}
