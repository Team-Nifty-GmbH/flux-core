<?php

use FluxErp\Actions\FixedAsset\BookFixedAssetDepreciations;
use FluxErp\Actions\FixedAsset\CreateFixedAsset;
use FluxErp\Actions\FixedAsset\DeleteFixedAsset;
use FluxErp\Actions\FixedAsset\DisposeFixedAsset;
use FluxErp\Actions\FixedAsset\ReverseFixedAssetDepreciations;
use FluxErp\Actions\FixedAsset\RevertFixedAssetDisposal;
use FluxErp\Actions\FixedAsset\UpdateFixedAsset;
use FluxErp\Actions\LedgerBooking\DeleteLedgerBooking;
use FluxErp\Enums\LedgerAccountTypeEnum;
use FluxErp\Models\FixedAsset;
use FluxErp\Models\FixedAssetDepreciation;
use FluxErp\Models\LedgerAccount;
use FluxErp\Models\LedgerBooking;
use Illuminate\Validation\ValidationException;

beforeEach(function (): void {
    $account = fn (LedgerAccountTypeEnum $type): int => LedgerAccount::factory()
        ->create(['tenant_id' => $this->dbTenant->getKey(), 'ledger_account_type_enum' => $type])
        ->getKey();

    $this->disposalAccountId = $account(LedgerAccountTypeEnum::Expense);
    $this->fixedAsset = CreateFixedAsset::make([
        'ledger_account_id' => $account(LedgerAccountTypeEnum::Asset),
        'depreciation_ledger_account_id' => $account(LedgerAccountTypeEnum::Expense),
        'name' => 'Delivery van',
        'acquired_at' => '2026-03-20',
        'acquisition_cost' => 36000,
        'useful_life_months' => 72,
    ])
        ->validate()
        ->execute();
});

function disposeFixedAsset(string $disposedAt, array $overrides = []): FixedAsset
{
    return DisposeFixedAsset::make(array_merge([
        'fixed_asset_id' => test()->fixedAsset->getKey(),
        'disposed_at' => $disposedAt,
        'disposal_ledger_account_id' => test()->disposalAccountId,
    ], $overrides))
        ->validate()
        ->execute();
}

test('disposal depreciates through the disposal month and books the remaining book value', function (): void {
    $fixedAsset = disposeFixedAsset('2026-07-10');

    $disposalBooking = $fixedAsset->ledgerBookings()->sole();

    expect($fixedAsset->book_value)->toEqual(0)
        ->and($fixedAsset->depreciations()->booked()->count())->toBe(5)
        ->and($disposalBooking)
        ->amount->toEqual(33500)
        ->debit_ledger_account_id->toBe($this->disposalAccountId)
        ->credit_ledger_account_id->toBe($fixedAsset->ledger_account_id)
        ->and($disposalBooking->booking_date->toDateString())->toBe('2026-07-10');
});

test('the depreciation of the disposal month is booked on the disposal date', function (): void {
    $fixedAsset = disposeFixedAsset('2026-07-10');

    $bookingDates = LedgerBooking::query()
        ->where('source_type', morph_alias(FixedAssetDepreciation::class))
        ->whereIntegerInRaw('source_id', $fixedAsset->depreciations()->pluck('id'))
        ->orderBy('booking_date')
        ->pluck('booking_date')
        ->map(fn ($date): string => $date->toDateString())
        ->all();

    expect($bookingDates)->toBe(['2026-03-31', '2026-04-30', '2026-05-31', '2026-06-30', '2026-07-10']);
});

test('disposal within the last month depreciated elsewhere only books the remaining book value', function (): void {
    $this->fixedAsset->update(['externally_depreciated_until' => '2026-06-30']);

    $fixedAsset = disposeFixedAsset('2026-06-15');

    expect($fixedAsset->depreciations()->booked()->count())->toBe(0)
        ->and($fixedAsset->ledgerBookings()->sole()->amount)->toEqual(34000)
        ->and($fixedAsset->book_value)->toEqual(0);
});

test('a disposed asset is not depreciated after the disposal month', function (): void {
    disposeFixedAsset('2026-07-10');

    expect(BookFixedAssetDepreciations::make(['until' => '2027-12-31'])->validate()->execute())->toBe(0);
});

test('reverting the disposal removes the disposal booking and resumes the plan', function (): void {
    disposeFixedAsset('2026-07-10');

    $fixedAsset = RevertFixedAssetDisposal::make(['fixed_asset_id' => $this->fixedAsset->getKey()])
        ->validate()
        ->execute();

    expect($fixedAsset->disposed_at)->toBeNull()
        ->and($fixedAsset->ledgerBookings()->count())->toBe(0)
        ->and($fixedAsset->book_value)->toEqual(33500)
        ->and(BookFixedAssetDepreciations::make(['until' => '2026-08-31'])->validate()->execute())->toBe(1);
});

test('disposal bookings cannot be deleted as ledger bookings', function (): void {
    $booking = disposeFixedAsset('2026-07-10')->ledgerBookings()->sole();

    expect(fn () => DeleteLedgerBooking::make(['id' => $booking->getKey()])
        ->validate()
        ->execute())
        ->toThrow(ValidationException::class)
        ->and(LedgerBooking::query()->whereKey($booking->getKey())->exists())->toBeTrue();
});

test('disposal is rejected when it does not fit', function (string $disposedAt, ?string $bookedUntil): void {
    if ($bookedUntil) {
        BookFixedAssetDepreciations::make(['until' => $bookedUntil])->validate()->execute();
    }

    disposeFixedAsset($disposedAt);
})
    ->with([
        'before the acquisition' => ['2026-01-31', null],
        'before booked months' => ['2026-04-15', '2026-06-30'],
    ])
    ->throws(ValidationException::class);

test('a disposed asset cannot be disposed again, reversed or deleted', function (): void {
    disposeFixedAsset('2026-07-10');

    expect(fn () => disposeFixedAsset('2026-08-10'))->toThrow(ValidationException::class)
        ->and(fn () => ReverseFixedAssetDepreciations::make([
            'fixed_asset_id' => $this->fixedAsset->getKey(),
            'from' => '2026-03-01',
        ])
            ->validate()
            ->execute())
        ->toThrow(ValidationException::class)
        ->and(fn () => DeleteFixedAsset::make(['id' => $this->fixedAsset->getKey()])
            ->validate()
            ->execute())
        ->toThrow(ValidationException::class);
});

test('disposal of a fully depreciated asset books nothing', function (): void {
    $this->travelTo('2032-06-01');
    $this->fixedAsset->update(['externally_depreciated_until' => '2032-02-29']);

    $fixedAsset = disposeFixedAsset('2032-05-10');

    expect($fixedAsset->ledgerBookings()->count())->toBe(0)
        ->and($fixedAsset->book_value)->toEqual(0)
        ->and($fixedAsset->disposed_at->toDateString())->toBe('2032-05-10');
});

test('a disposed asset cannot be changed beyond its name', function (): void {
    disposeFixedAsset('2026-07-10');

    $renamed = UpdateFixedAsset::make(['id' => $this->fixedAsset->getKey(), 'name' => 'Sold van'])
        ->validate()
        ->execute();

    expect($renamed->name)->toBe('Sold van')
        ->and(fn () => UpdateFixedAsset::make(['id' => $this->fixedAsset->getKey(), 'acquisition_cost' => 40000])
            ->validate()
            ->execute())
        ->toThrow(ValidationException::class);
});

test('disposal is rejected in the future or before the months depreciated elsewhere', function (): void {
    $this->travelTo('2026-08-15');

    expect(fn () => disposeFixedAsset('2026-09-01'))->toThrow(ValidationException::class);

    $this->fixedAsset->update(['externally_depreciated_until' => '2026-06-30']);

    expect(fn () => disposeFixedAsset('2026-05-10'))->toThrow(ValidationException::class);
});
