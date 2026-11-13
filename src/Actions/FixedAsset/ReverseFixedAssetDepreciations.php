<?php

namespace FluxErp\Actions\FixedAsset;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\FixedAsset;
use FluxErp\Models\FixedAssetDepreciation;
use FluxErp\Models\LedgerBooking;
use FluxErp\Rulesets\FixedAsset\ReverseFixedAssetDepreciationsRuleset;
use Illuminate\Validation\ValidationException;

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

        $ledgerBookings->each(fn (LedgerBooking $ledgerBooking): ?bool => $ledgerBooking->delete());

        return $ledgerBookings->count();
    }

    protected function validateData(): void
    {
        parent::validateData();

        if (resolve_static(FixedAsset::class, 'query')
            ->whereKey($this->getData('fixed_asset_id'))
            ->whereNotNull('disposed_at')
            ->exists()
        ) {
            throw ValidationException::withMessages([
                'fixed_asset_id' => [__('Revert the disposal before reversing depreciations.')],
            ]);
        }
    }
}
