<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Permission;
use FluxErp\Models\Role;
use FluxErp\Traits\HasPackageFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoleHasPermission extends FluxPivot
{
    use HasPackageFactory;

    public $incrementing = false;

    public $timestamps = false;

    protected $primaryKey = ['role_id', 'permission_id'];

    protected $table = 'role_has_permission';

    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}
