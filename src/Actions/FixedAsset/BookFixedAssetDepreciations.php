<?php

namespace FluxErp\Actions\FixedAsset;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\FixedAsset;
use FluxErp\Models\FixedAssetDepreciation;
use FluxErp\Models\LedgerBooking;
use FluxErp\Rulesets\FixedAsset\BookFixedAssetDepreciationsRuleset;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class BookFixedAssetDepreciations extends FluxAction
{
    public static function models(): array
    {
        return [FixedAsset::class];
    }

    protected function getRulesets(): string|array
    {
        return BookFixedAssetDepreciationsRuleset::class;
    }

    public function performAction(): int
    {
        $fixedAssets = resolve_static(FixedAsset::class, 'query')
            ->when(
                $this->getData('fixed_asset_id'),
                fn (Builder $query) => $query->whereKey($this->getData('fixed_asset_id'))
            )
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        return $fixedAssets->sum(fn (FixedAsset $fixedAsset): int => $this->bookFixedAsset($fixedAsset));
    }

    protected function bookFixedAsset(FixedAsset $fixedAsset): int
    {
        $depreciations = $this->due($fixedAsset->depreciations()->getQuery())
            ->orderBy('sequence')
            ->get(['id', 'booking_date', 'amount']);

        if ($depreciations->isEmpty()) {
            return 0;
        }

        foreach ($depreciations as $depreciation) {
            app(LedgerBooking::class, [
                'attributes' => [
                    'tenant_id' => $fixedAsset->tenant_id,
                    'debit_ledger_account_id' => $fixedAsset->depreciation_ledger_account_id,
                    'credit_ledger_account_id' => $fixedAsset->ledger_account_id,
                    'source_type' => morph_alias(FixedAssetDepreciation::class),
                    'source_id' => $depreciation->getKey(),
                    'amount' => $depreciation->getRawOriginal('amount'),
                    'booking_date' => $depreciation->booking_date,
                    'booking_text' => Str::limit(trim($fixedAsset->number . ' ' . $fixedAsset->name), 255, ''),
                ],
            ])
                ->save();
        }

        $fixedAsset->calculateBookValue()
            ->save();

        return $depreciations->count();
    }

    protected function due(Builder $query): Builder
    {
        return $query->whereDate('booking_date', '<=', $this->getData('until'))
            ->unbooked();
    }

    protected function prepareForValidation(): void
    {
        $this->data['until'] ??= now()->toDateString();
    }
}
