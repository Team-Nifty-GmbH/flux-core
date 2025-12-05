<?php

namespace FluxErp\Models;

use FluxErp\Models\Pivots\PaymentTypeTenant;
use FluxErp\Traits\Model\Filterable;
use FluxErp\Traits\Model\HasAttributeTranslations;
use FluxErp\Traits\Model\HasDefault;
use FluxErp\Traits\Model\HasPackageFactory;
use FluxErp\Traits\Model\HasTenantAssignment;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\HasUuid;
use FluxErp\Traits\Model\LogsActivity;
use FluxErp\Traits\Model\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PaymentType extends FluxModel
{
    use Filterable, HasAttributeTranslations, HasDefault, HasPackageFactory, HasTenantAssignment, HasUserModification,
        HasUuid, LogsActivity, SoftDeletes;

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
        return $this->belongsToMany(Tenant::class, 'payment_type_tenant')
            ->using(PaymentTypeTenant::class);
    }

    protected function translatableAttributes(): array
    {
        return [
            'description',
        ];
    }
}
