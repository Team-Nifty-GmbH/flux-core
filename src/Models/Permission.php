<?php

namespace FluxErp\Models;

use FluxErp\Traits\Filterable;
use FluxErp\Traits\ResolvesRelationsThroughContainer;
use FluxErp\Traits\Scout\Searchable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    use Filterable, ResolvesRelationsThroughContainer, Searchable;

    protected $guarded = [
        'id',
    ];

    protected $hidden = ['pivot'];

    public function users(): BelongsToMany
    {
        return $this->morphedByMany(
            User::class,
            'model',
            config('permission.table_names.model_has_permissions'),
            config('permission.column_names.permission_pivot_key'),
            config('permission.column_names.model_morph_key')
        );
    }
}
