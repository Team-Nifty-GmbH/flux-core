<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Contact;
use FluxErp\Models\Tenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactTenant extends FluxPivot
{
    protected $table = 'contact_tenant';

    // Relations
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}
