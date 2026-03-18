<?php

namespace FluxErp\Models;

use FluxErp\Models\Pivots\AddressTypeTenant;
use FluxErp\Traits\Model\HasPackageFactory;
use FluxErp\Traits\Model\HasTenantAssignment;
use FluxErp\Traits\Model\HasTenants;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\HasUuid;
use FluxErp\Traits\Model\LogsActivity;
use FluxErp\Traits\Model\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AddressType extends FluxModel
{
    use HasPackageFactory, HasTenantAssignment, HasTenants, HasUserModification, HasUuid, LogsActivity, SoftDeletes;

    protected function casts(): array
    {
        return [
            'is_locked' => 'boolean',
            'is_unique' => 'boolean',
        ];
    }

    public function addresses(): BelongsToMany
    {
        return $this->belongsToMany(Address::class);
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'address_address_type_order')->withPivot('address_id');
    }

    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'address_type_tenant')->using(AddressTypeTenant::class);
    }
}
