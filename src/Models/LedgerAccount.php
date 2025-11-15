<?php

namespace FluxErp\Models;

use FluxErp\Enums\LedgerAccountTypeEnum;
use FluxErp\Traits\CacheModelQueries;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasTenantAssignment;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\Scout\Searchable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LedgerAccount extends FluxModel
{
    use CacheModelQueries, HasPackageFactory, HasTenantAssignment, HasUuid;
    use Searchable {
        Searchable::scoutIndexSettings as baseScoutIndexSettings;
    }

    public static function scoutIndexSettings(): ?array
    {
        return static::baseScoutIndexSettings() ?? [
            'filterableAttributes' => [
                'ledger_account_type_enum',
                'is_automatic',
            ],
            'sortableAttributes' => ['*'],
        ];
    }

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
