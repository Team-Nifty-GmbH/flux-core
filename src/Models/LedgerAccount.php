<?php

namespace FluxErp\Models;

use FluxErp\Enums\LedgerAccountTypeEnum;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;

class LedgerAccount extends Model
{
    use HasPackageFactory, HasUuid, Searchable;

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
