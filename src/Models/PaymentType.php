<?php

namespace FluxErp\Models;

use FluxErp\Traits\CacheModelQueries;
use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasClientAssignment;
use FluxErp\Traits\HasDefault;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\LogsActivity;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentType extends Model
{
    use CacheModelQueries, Filterable, HasClientAssignment, HasDefault, HasPackageFactory,
        HasUserModification, HasUuid, LogsActivity, SoftDeletes;

    protected $guarded = [
        'id',
    ];

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

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function paymentNotices(): HasMany
    {
        return $this->hasMany(PaymentNotice::class, 'payment_type_id');
    }
}
