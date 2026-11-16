<?php

namespace FluxErp\Livewire\Accounting;

use FluxErp\Livewire\DataTables\LedgerBookingList;
use FluxErp\Models\FixedAssetDepreciation;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Modelable;

class FixedAssetLedgerBookings extends LedgerBookingList
{
    #[Modelable]
    public ?int $fixedAssetId = null;

    public function getBuilder(Builder $builder): Builder
    {
        return parent::getBuilder($builder)
            ->where('source_type', morph_alias(FixedAssetDepreciation::class))
            ->whereIn(
                'source_id',
                resolve_static(FixedAssetDepreciation::class, 'query')
                    ->select('id')
                    ->where('fixed_asset_id', $this->fixedAssetId)
            );
    }
}
