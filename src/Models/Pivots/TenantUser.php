<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Tenant;
use FluxErp\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantUser extends FluxPivot
{
    protected $table = 'tenant_user';

    // Relations
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
