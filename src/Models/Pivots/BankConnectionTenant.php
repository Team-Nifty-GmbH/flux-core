<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\BankConnection;
use FluxErp\Models\Tenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankConnectionTenant extends FluxPivot
{
    protected $table = 'bank_connection_tenant';

    public function bankConnection(): BelongsTo
    {
        return $this->belongsTo(BankConnection::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
