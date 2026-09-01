<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\BankConnection;
use FluxErp\Models\Tenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankConnectionTenant extends FluxPivot
{
    protected $table = 'bank_connection_tenant';

    // Relations
    /**
     * @return BelongsTo<BankConnection, $this>
     */
    public function bankConnection(): BelongsTo
    {
        return $this->belongsTo(BankConnection::class);
    }

    /**
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
