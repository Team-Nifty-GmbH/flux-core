<?php

namespace FluxErp\Actions\FixedAsset;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\FixedAsset;
use FluxErp\Models\FixedAssetDepreciation;
use FluxErp\Models\LedgerBooking;
use FluxErp\Rulesets\FixedAsset\ReverseFixedAssetDepreciationsRuleset;

class ReverseFixedAssetDepreciations extends FluxAction
{
    public static function models(): array
    {
        return [FixedAsset::class];
    }

    protected function getRulesets(): string|array
    {
        return ReverseFixedAssetDepreciationsRuleset::class;
    }

    public function performAction(): int
    {
        $fixedAsset = resolve_static(FixedAsset::class, 'query')
            ->whereKey($this->getData('fixed_asset_id'))
            ->lockForUpdate()
            ->firstOrFail();

        $ledgerBookings = resolve_static(LedgerBooking::class, 'query')
            ->where('source_type', morph_alias(FixedAssetDepreciation::class))
            ->whereIn(
                'source_id',
                $fixedAsset->depreciations()
                    ->whereDate('booking_date', '>=', $this->getData('from'))
                    ->select('id')
            )
            ->get();

        $ledgerBookings->each(fn (LedgerBooking $ledgerBooking) => $ledgerBooking->delete());

        $fixedAsset->calculateBookValue()
            ->save();

        return $ledgerBookings->count();
    }
}
