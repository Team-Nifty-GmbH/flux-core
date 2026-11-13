<?php

namespace FluxErp\Actions\FixedAsset;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\FixedAsset;
use FluxErp\Models\LedgerBooking;
use FluxErp\Rulesets\FixedAsset\RevertFixedAssetDisposalRuleset;
use Illuminate\Validation\ValidationException;

class RevertFixedAssetDisposal extends FluxAction
{
    public static function models(): array
    {
        return [FixedAsset::class];
    }

    protected function getRulesets(): string|array
    {
        return RevertFixedAssetDisposalRuleset::class;
    }

    public function performAction(): FixedAsset
    {
        $fixedAsset = resolve_static(FixedAsset::class, 'query')
            ->whereKey($this->getData('fixed_asset_id'))
            ->lockForUpdate()
            ->firstOrFail();

        $this->ensureDisposed($fixedAsset);

        $fixedAsset->ledgerBookings()
            ->get()
            ->each(fn (LedgerBooking $ledgerBooking): ?bool => $ledgerBooking->delete());

        $fixedAsset->fill([
            'disposed_at' => null,
            'disposal_ledger_account_id' => null,
        ]);
        $fixedAsset->calculateBookValue()
            ->save();

        return $fixedAsset->refresh();
    }

    protected function ensureDisposed(FixedAsset $fixedAsset): void
    {
        if (! $fixedAsset->disposed_at) {
            throw ValidationException::withMessages([
                'fixed_asset_id' => [__('The fixed asset has not been disposed of.')],
            ]);
        }
    }

    protected function validateData(): void
    {
        parent::validateData();

        $this->ensureDisposed(
            resolve_static(FixedAsset::class, 'query')
                ->whereKey($this->getData('fixed_asset_id'))
                ->firstOrFail()
        );
    }
}
