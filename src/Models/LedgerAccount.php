<?php

namespace FluxErp\Models;

use FluxErp\Enums\LedgerAccountTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LedgerAccount extends Model
{
    use HasFactory;

    protected $casts = [
        'ledger_account_type_enum' => LedgerAccountTypeEnum::class,
    ];

    protected $guarded = [
        'id',
    ];

    public function orderPositions(): HasMany
    {
        return $this->hasMany(OrderPosition::class);
    }
}
