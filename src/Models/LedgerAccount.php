<?php

namespace FluxErp\Models;

use FluxErp\Enums\LedgerAccountTypeEnum;
use FluxErp\Traits\CacheModelQueries;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;

class LedgerAccount extends Model
{
    use CacheModelQueries, HasPackageFactory, HasUuid, Searchable;

    protected $guarded = [
        'id',
    ];

    protected function casts(): array
    {
        return [
            'ledger_account_type_enum' => LedgerAccountTypeEnum::class,
            'is_automatic' => 'boolean',
        ];
    }

    public function orderPositions(): HasMany
    {
        return $this->hasMany(OrderPosition::class);
    }
}
