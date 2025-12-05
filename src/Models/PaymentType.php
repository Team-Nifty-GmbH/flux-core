<?php

namespace FluxErp\Models;

use FluxErp\Models\Pivots\ClientPaymentType;
use FluxErp\Traits\Model\Filterable;
use FluxErp\Traits\Model\HasAttributeTranslations;
use FluxErp\Traits\Model\HasClientAssignment;
use FluxErp\Traits\Model\HasDefault;
use FluxErp\Traits\Model\HasPackageFactory;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\HasUuid;
use FluxErp\Traits\Model\LogsActivity;
use FluxErp\Traits\Model\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PaymentType extends FluxModel
{
    use Filterable, HasAttributeTranslations, HasClientAssignment, HasDefault, HasPackageFactory, HasUserModification,
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

    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class, 'client_payment_type')
            ->using(ClientPaymentType::class);
    }

    protected function translatableAttributes(): array
    {
        return [
            'description',
        ];
    }
}
