<?php

use FluxErp\Actions\FixedAsset\BookFixedAssetDepreciations;
use FluxErp\Actions\FixedAsset\CreateFixedAsset;
use FluxErp\Actions\FixedAsset\ReverseFixedAssetDepreciations;
use FluxErp\Actions\FixedAsset\UpdateFixedAsset;
use FluxErp\Actions\LedgerBooking\CreateLedgerBooking;
use FluxErp\Actions\LedgerBooking\DeleteLedgerBooking;
use FluxErp\Actions\LedgerBooking\UpdateLedgerBooking;
use FluxErp\Enums\LedgerAccountTypeEnum;
use FluxErp\Jobs\Accounting\BookFixedAssetDepreciationsJob;
use FluxErp\Models\FixedAsset;
use FluxErp\Models\FixedAssetDepreciation;
use FluxErp\Models\LedgerAccount;
use FluxErp\Models\LedgerBooking;
use FluxErp\Models\Tenant;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

function createDepreciableAsset(Tenant $tenant, array $overrides = []): FixedAsset
{
    return CreateFixedAsset::make(array_merge([
        'tenant_id' => $tenant->getKey(),
        'ledger_account_id' => LedgerAccount::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'ledger_account_type_enum' => LedgerAccountTypeEnum::Asset,
        ])->getKey(),
        'depreciation_ledger_account_id' => LedgerAccount::factory()->create([
            'tenant_id' => $tenant->getKey(),
            'ledger_account_type_enum' => LedgerAccountTypeEnum::Expense,
        ])->getKey(),
        'name' => 'Delivery van',
        'number' => 'FA-0001',
        'acquired_at' => '2026-03-20',
        'acquisition_cost' => 36000,
        'useful_life_months' => 72,
    ], $overrides))
        ->validate()
        ->execute();
}

function bookDepreciations(array $data): int
{
    return BookFixedAssetDepreciations::make($data)
        ->validate()
        ->execute();
}

function depreciationBookings(FixedAsset $fixedAsset): Collection
{
    return LedgerBooking::query()
        ->where('source_type', morph_alias(FixedAssetDepreciation::class))
        ->whereIn('source_id', $fixedAsset->depreciations()->pluck('id'))
        ->orderBy('booking_date')
        ->get();
}

beforeEach(function (): void {
    $this->fixedAsset = createDepreciableAsset($this->dbTenant);
});

test('books only the depreciations that are due', function (): void {
    expect(bookDepreciations(['until' => '2026-05-31']))->toBe(3);

    $bookings = depreciationBookings($this->fixedAsset);

    expect($bookings)->toHaveCount(3)
        ->and($bookings->pluck('booking_date')->map->toDateString()->all())
        ->toBe(['2026-03-31', '2026-04-30', '2026-05-31'])
        ->and($bookings->first())
        ->debit_ledger_account_id->toBe($this->fixedAsset->depreciation_ledger_account_id)
        ->credit_ledger_account_id->toBe($this->fixedAsset->ledger_account_id)
        ->amount->toEqual(500)
        ->booking_text->toBe('FA-0001 Delivery van')
        ->and($this->fixedAsset->refresh()->book_value)->toEqual(34500);
});

test('never books a depreciation twice', function (): void {
    bookDepreciations(['until' => '2026-05-31']);

    expect(bookDepreciations(['until' => '2026-05-31']))->toBe(0)
        ->and(bookDepreciations(['until' => '2026-06-30']))->toBe(1)
        ->and(depreciationBookings($this->fixedAsset))->toHaveCount(4);
});

test('books on the tenant and accounts of the fixed asset', function (): void {
    $otherTenant = Tenant::factory()->create();
    $foreignAsset = createDepreciableAsset($otherTenant);

    bookDepreciations(['until' => '2026-03-31', 'fixed_asset_id' => $foreignAsset->getKey()]);

    expect(depreciationBookings($this->fixedAsset))->toHaveCount(0)
        ->and(depreciationBookings($foreignAsset)->sole())
        ->tenant_id->toBe($otherTenant->getKey())
        ->debit_ledger_account_id->toBe($foreignAsset->depreciation_ledger_account_id)
        ->credit_ledger_account_id->toBe($foreignAsset->ledger_account_id);
});

test('reversing removes the bookings from the given month on and unlocks the schedule', function (): void {
    bookDepreciations(['until' => '2026-05-31']);

    expect(ReverseFixedAssetDepreciations::make([
        'fixed_asset_id' => $this->fixedAsset->getKey(),
        'from' => '2026-04-01',
    ])
        ->validate()
        ->execute())->toBe(2)
        ->and(depreciationBookings($this->fixedAsset))->toHaveCount(1)
        ->and($this->fixedAsset->refresh()->book_value)->toEqual(35500);

    ReverseFixedAssetDepreciations::make([
        'fixed_asset_id' => $this->fixedAsset->getKey(),
        'from' => '2026-03-01',
    ])
        ->validate()
        ->execute();

    UpdateFixedAsset::make(['id' => $this->fixedAsset->getKey(), 'useful_life_months' => 36])
        ->validate()
        ->execute();

    expect($this->fixedAsset->depreciations()->count())->toBe(36);
});

test('reversed depreciations are booked again on the next run', function (): void {
    bookDepreciations(['until' => '2026-04-30']);
    $reversed = depreciationBookings($this->fixedAsset)->last();

    ReverseFixedAssetDepreciations::make([
        'fixed_asset_id' => $this->fixedAsset->getKey(),
        'from' => '2026-04-01',
    ])
        ->validate()
        ->execute();

    expect(bookDepreciations(['until' => '2026-04-30']))->toBe(1)
        ->and(depreciationBookings($this->fixedAsset))->toHaveCount(2)
        ->and(depreciationBookings($this->fixedAsset)->last()->getKey())->not->toBe($reversed->getKey())
        ->and($this->fixedAsset->refresh()->book_value)->toEqual(35000);
});

test('depreciation bookings cannot be changed or deleted as ledger bookings', function (): void {
    bookDepreciations(['until' => '2026-03-31']);
    $booking = depreciationBookings($this->fixedAsset)->sole();

    expect(fn () => UpdateLedgerBooking::make(['id' => $booking->getKey(), 'amount' => 1])
        ->validate()
        ->execute())
        ->toThrow(ValidationException::class)
        ->and(fn () => DeleteLedgerBooking::make(['id' => $booking->getKey()])
            ->validate()
            ->execute())
        ->toThrow(ValidationException::class)
        ->and(fn () => CreateLedgerBooking::make([
            'debit_ledger_account_id' => $booking->debit_ledger_account_id,
            'credit_ledger_account_id' => $booking->credit_ledger_account_id,
            'source_type' => morph_alias(FixedAssetDepreciation::class),
            'source_id' => $this->fixedAsset->depreciations()->orderByDesc('sequence')->value('id'),
            'amount' => 1,
            'booking_date' => '2026-04-30',
        ])->validate()->execute())
        ->toThrow(ValidationException::class)
        ->and($booking->refresh()->amount)->toEqual(500);
});

test('skips the depreciations booked outside of flux', function (): void {
    UpdateFixedAsset::make(['id' => $this->fixedAsset->getKey(), 'externally_depreciated_until' => '2026-04-30'])
        ->validate()
        ->execute();

    expect(bookDepreciations(['until' => '2026-05-31']))->toBe(1)
        ->and(depreciationBookings($this->fixedAsset)->sole()->booking_date->toDateString())->toBe('2026-05-31')
        ->and($this->fixedAsset->refresh()->book_value)->toEqual(34500);
});

test('refuses to book a single asset onto a ledger account that is no longer usable', function (): void {
    LedgerAccount::query()
        ->whereKey($this->fixedAsset->depreciation_ledger_account_id)
        ->first()
        ->delete();

    expect(fn () => bookDepreciations([
        'fixed_asset_id' => $this->fixedAsset->getKey(),
        'until' => '2026-05-31',
    ]))
        ->toThrow(ValidationException::class)
        ->and(depreciationBookings($this->fixedAsset))->toHaveCount(0);
});

test('the run skips an asset whose ledger account is no longer usable and books the others', function (): void {
    $otherAsset = createDepreciableAsset($this->dbTenant);
    LedgerAccount::query()
        ->whereKey($this->fixedAsset->depreciation_ledger_account_id)
        ->first()
        ->delete();

    expect(bookDepreciations(['until' => '2026-05-31']))->toBe(3)
        ->and(depreciationBookings($this->fixedAsset))->toHaveCount(0)
        ->and(depreciationBookings($otherAsset))->toHaveCount(3);
});

test('the class name does not get around the depreciation booking guard', function (): void {
    CreateLedgerBooking::make([
        'debit_ledger_account_id' => $this->fixedAsset->depreciation_ledger_account_id,
        'credit_ledger_account_id' => $this->fixedAsset->ledger_account_id,
        'source_type' => FixedAssetDepreciation::class,
        'source_id' => $this->fixedAsset->depreciations()->value('id'),
        'amount' => 1,
        'booking_date' => '2026-04-30',
    ])
        ->validate()
        ->execute();
})->throws(ValidationException::class);

test('the repeatable job runs on the first of each month', function (): void {
    expect(BookFixedAssetDepreciationsJob::defaultCron()?->getExpression())->toBe('0 2 1 * *');
});

test('the repeatable job books everything due until today', function (): void {
    $this->travelTo('2026-06-01 02:00:00');

    (new BookFixedAssetDepreciationsJob())->handle();

    expect(depreciationBookings($this->fixedAsset))->toHaveCount(3);
});
