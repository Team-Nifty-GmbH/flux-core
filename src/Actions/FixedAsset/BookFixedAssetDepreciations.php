<?php

namespace FluxErp\Actions\FixedAsset;

use FluxErp\Actions\FluxAction;
use FluxErp\Enums\LedgerAccountTypeEnum;
use FluxErp\Models\FixedAsset;
use FluxErp\Models\FixedAssetDepreciation;
use FluxErp\Models\LedgerAccount;
use FluxErp\Models\LedgerBooking;
use FluxErp\Rulesets\FixedAsset\BookFixedAssetDepreciationsRuleset;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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
                fn (Builder $query): Builder => $query->whereKey($this->getData('fixed_asset_id'))
            )
            ->whereHas('depreciations', fn (Builder $query): Builder => $this->due($query))
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        return $fixedAssets->sum(fn (FixedAsset $fixedAsset): int => $this->bookFixedAsset($fixedAsset));
    }

    protected function bookFixedAsset(FixedAsset $fixedAsset): int
    {
        if (! $this->hasUsableAccounts($fixedAsset)) {
            if (! $this->getData('fixed_asset_id')) {
                return 0;
            }

            throw ValidationException::withMessages([
                'fixed_asset_id' => [
                    __('The ledger accounts of fixed asset :name can no longer be booked on.', [
                        'name' => $fixedAsset->name,
                    ]),
                ],
            ]);
        }

        $depreciations = $this->due($fixedAsset->depreciations()->getQuery())
            ->orderBy('sequence')
            ->get(['id', 'booking_date', 'amount']);

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

        return $depreciations->count();
    }

    protected function due(Builder $query): Builder
    {
        return $query->whereDate('booking_date', '<=', $this->getData('until'))
            ->undepreciated();
    }

    protected function hasUsableAccounts(FixedAsset $fixedAsset): bool
    {
        return resolve_static(LedgerAccount::class, 'query')
            ->withoutGlobalScopes()
            ->whereNull('deleted_at')
            ->where('tenant_id', $fixedAsset->tenant_id)
            ->where(fn (Builder $query): Builder => $query
                ->where(fn (Builder $query): Builder => $query
                    ->whereKey($fixedAsset->ledger_account_id)
                    ->where('ledger_account_type_enum', LedgerAccountTypeEnum::Asset)
                )
                ->orWhere(fn (Builder $query): Builder => $query
                    ->whereKey($fixedAsset->depreciation_ledger_account_id)
                    ->where('ledger_account_type_enum', LedgerAccountTypeEnum::Expense)
                )
            )
            ->count() === 2;
    }

    protected function prepareForValidation(): void
    {
        $this->data['until'] ??= now()->toDateString();
    }
}
