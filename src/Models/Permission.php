<?php

namespace FluxErp\Models;

use FluxErp\Traits\Filterable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Scout\Searchable;
use Spatie\Permission\Models\Permission as SpatiePermission;
use Spatie\Permission\PermissionRegistrar;

class Permission extends SpatiePermission
{
    use Filterable, Searchable;

    protected $hidden = ['pivot'];

    public function users(): BelongsToMany
    {
        return $this->morphedByMany(
            User::class,
            'model',
            config('permission.table_names.model_has_permissions'),
            PermissionRegistrar::$pivotPermission,
            config('permission.column_names.model_morph_key')
        );
    }
}
