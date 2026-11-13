<?php

namespace FluxErp\Actions\FixedAsset;

use Carbon\Carbon;
use FluxErp\Actions\FluxAction;
use FluxErp\Enums\LedgerAccountTypeEnum;
use FluxErp\Models\FixedAsset;
use FluxErp\Models\LedgerAccount;
use FluxErp\Models\LedgerBooking;
use FluxErp\Rulesets\FixedAsset\DisposeFixedAssetRuleset;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DisposeFixedAsset extends FluxAction
{
    public static function models(): array
    {
        return [FixedAsset::class];
    }

    protected function getRulesets(): string|array
    {
        return DisposeFixedAssetRuleset::class;
    }

    public function performAction(): FixedAsset
    {
        $fixedAsset = resolve_static(FixedAsset::class, 'query')
            ->whereKey($this->getData('fixed_asset_id'))
            ->lockForUpdate()
            ->firstOrFail();

        $this->ensureDisposable($fixedAsset);

        $fixedAsset->fill([
            'disposed_at' => $this->getData('disposed_at'),
            'disposal_ledger_account_id' => $this->getData('disposal_ledger_account_id'),
        ]);
        $fixedAsset->save();

        BookFixedAssetDepreciations::make([
            'fixed_asset_id' => $fixedAsset->getKey(),
            'until' => Carbon::parse($this->getData('disposed_at'))
                ->endOfMonth()
                ->toDateString(),
        ])
            ->validate()
            ->execute();

        $fixedAsset->refresh()
            ->recalculateBookValue();
        $bookValue = $fixedAsset->book_value;

        if (bccomp((string) $bookValue, '0', 2) === 1) {
            app(LedgerBooking::class, [
                'attributes' => [
                    'tenant_id' => $fixedAsset->tenant_id,
                    'debit_ledger_account_id' => $fixedAsset->disposal_ledger_account_id,
                    'credit_ledger_account_id' => $fixedAsset->ledger_account_id,
                    'source_type' => morph_alias(FixedAsset::class),
                    'source_id' => $fixedAsset->getKey(),
                    'amount' => bcround((string) $bookValue, 2),
                    'booking_date' => $fixedAsset->disposed_at,
                    'booking_text' => Str::limit(
                        trim(__('Disposal') . ' ' . $fixedAsset->number . ' ' . $fixedAsset->name),
                        255,
                        ''
                    ),
                ],
            ])
                ->save();
        }

        return $fixedAsset->refresh();
    }

    protected function ensureDisposable(FixedAsset $fixedAsset): void
    {
        $errors = [];

        if ($fixedAsset->disposed_at) {
            $errors['fixed_asset_id'][] = __('The fixed asset has already been disposed of.');
        }

        $disposedAt = Carbon::parse($this->getData('disposed_at'));

        if ($disposedAt->lt($fixedAsset->acquired_at)) {
            $errors['disposed_at'][] = __('The disposal cannot be before the acquisition.');
        }

        if ($fixedAsset->externally_depreciated_until && $disposedAt->lt($fixedAsset->externally_depreciated_until)) {
            $errors['disposed_at'][] = __('The disposal cannot be before the months depreciated elsewhere.');
        }

        if ($fixedAsset->depreciations()
            ->booked()
            ->whereDate('booking_date', '>', $disposedAt->copy()->endOfMonth())
            ->exists()
        ) {
            $errors['disposed_at'][] = __('Reverse the depreciations booked after the disposal month first.');
        }

        foreach ([
            'disposal_ledger_account_id' => [
                $this->getData('disposal_ledger_account_id'),
                LedgerAccountTypeEnum::Expense,
            ],
            'ledger_account_id' => [$fixedAsset->ledger_account_id, LedgerAccountTypeEnum::Asset],
        ] as $attribute => [$ledgerAccountId, $type]) {
            if (resolve_static(LedgerAccount::class, 'query')
                ->withoutGlobalScopes()
                ->whereNull('deleted_at')
                ->whereKey($ledgerAccountId)
                ->where('tenant_id', $fixedAsset->tenant_id)
                ->where('ledger_account_type_enum', $type)
                ->doesntExist()
            ) {
                $errors[$attribute][] = __(
                    'The selected :attribute must be a :type account of the fixed asset tenant.',
                    ['attribute' => $attribute, 'type' => __($type->name)]
                );
            }
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }
    }

    protected function validateData(): void
    {
        parent::validateData();

        $this->ensureDisposable(
            resolve_static(FixedAsset::class, 'query')
                ->whereKey($this->getData('fixed_asset_id'))
                ->firstOrFail()
        );
    }
}
