<?php

namespace FluxErp\Models;

use FluxErp\Models\Pivots\TenantPaymentType;
use FluxErp\Traits\CacheModelQueries;
use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasAttributeTranslations;
use FluxErp\Traits\HasDefault;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasTenantAssignment;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\LogsActivity;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PaymentType extends FluxModel
{
    use CacheModelQueries, Filterable, HasAttributeTranslations, HasDefault, HasPackageFactory, HasTenantAssignment,
        HasUserModification, HasUuid, LogsActivity, SoftDeletes;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_direct_debit' => 'boolean',
            'is_default' => 'boolean',
            'is_purchase' => 'boolean',
            'is_sales' => 'boolean',
            'requires_manual_transfer' => 'boolean',
        ];
    }

    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'tenant_payment_type')
            ->using(TenantPaymentType::class);
    }

    protected function translatableAttributes(): array
    {
        return [
            'description',
        ];
    }
}
