<?php

namespace FluxErp\Models;

use FluxErp\Enums\LedgerAccountTypeEnum;
use FluxErp\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LedgerAccount extends Model
{
    use HasFactory, HasUuid;

    protected $casts = [
        'ledger_account_type_enum' => LedgerAccountTypeEnum::class,
        'is_automatic' => 'boolean',
    ];

    protected $guarded = [
        'id',
    ];

    public function orderPositions(): HasMany
    {
        return $this->hasMany(OrderPosition::class);
    }
}
