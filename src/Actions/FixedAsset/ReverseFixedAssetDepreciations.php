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

        $this->ensureNotDisposed($fixedAsset);

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

    protected function ensureNotDisposed(FixedAsset $fixedAsset): void
    {
        if ($fixedAsset->disposed_at) {
            throw ValidationException::withMessages([
                'fixed_asset_id' => [__('Revert the disposal before reversing depreciations.')],
            ]);
        }
    }

    protected function validateData(): void
    {
        parent::validateData();

        $this->ensureNotDisposed(
            resolve_static(FixedAsset::class, 'query')
                ->whereKey($this->getData('fixed_asset_id'))
                ->firstOrFail(['id', 'disposed_at'])
        );
    }
}
