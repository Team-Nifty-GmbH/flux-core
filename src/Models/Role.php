<?php

namespace FluxErp\Models;

use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasPackageFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use Filterable, HasPackageFactory;

    protected $hidden = ['pivot'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class,
            'model_has_roles', 'role_id', 'model_id'
        )->withPivotValue('model_type', User::class);
    }
}
