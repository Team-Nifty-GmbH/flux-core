<?php

use FluxErp\Actions\FixedAsset\CreateFixedAsset;
use FluxErp\Enums\LedgerAccountTypeEnum;
use FluxErp\Livewire\Accounting\FixedAsset;
use FluxErp\Livewire\Accounting\FixedAssetDepreciations;
use FluxErp\Livewire\Accounting\FixedAssetLedgerBookings;
use FluxErp\Models\LedgerAccount;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->fixedAsset = CreateFixedAsset::make([
        'ledger_account_id' => LedgerAccount::factory()->create([
            'tenant_id' => $this->dbTenant->getKey(),
            'ledger_account_type_enum' => LedgerAccountTypeEnum::Asset,
        ])->getKey(),
        'depreciation_ledger_account_id' => LedgerAccount::factory()->create([
            'tenant_id' => $this->dbTenant->getKey(),
            'ledger_account_type_enum' => LedgerAccountTypeEnum::Expense,
        ])->getKey(),
        'name' => 'Delivery van',
        'acquired_at' => '2026-03-20',
        'acquisition_cost' => 36000,
        'useful_life_months' => 72,
    ])
        ->validate()
        ->execute();
});

test('renders every tab', function (string $tab): void {
    Livewire::withQueryParams(['tab' => $tab])
        ->test(FixedAsset::class, ['id' => $this->fixedAsset->getKey()])
        ->assertOk()
        ->assertSet('fixedAsset.name', 'Delivery van');
})->with(['fixed-asset.general', 'fixed-asset.depreciations', 'fixed-asset.bookings']);

test('books, reverses and lists the depreciations', function (): void {
    $this->travelTo('2026-05-15');

    Livewire::test(FixedAsset::class, ['id' => $this->fixedAsset->getKey()])
        ->call('book')
        ->assertHasNoErrors()
        ->assertSet('fixedAsset.book_value', 35000.0);

    $bookings = Livewire::test(FixedAssetLedgerBookings::class, ['fixedAssetId' => $this->fixedAsset->getKey()])
        ->call('loadData')
        ->assertOk()
        ->instance()
        ->getDataForTesting();
    $depreciations = Livewire::test(FixedAssetDepreciations::class, ['fixedAssetId' => $this->fixedAsset->getKey()])
        ->call('loadData')
        ->assertOk()
        ->instance()
        ->getDataForTesting();

    expect(data_get($bookings, 'data'))->toHaveCount(2)
        ->and(data_get($depreciations, 'data.0.status.raw'))->toBe('Booked')
        ->and(data_get($depreciations, 'data.2.status.raw'))->toBe('Open');

    Livewire::test(FixedAsset::class, ['id' => $this->fixedAsset->getKey()])
        ->set('reverseFrom', '2026-04-01')
        ->call('reverse')
        ->assertHasNoErrors()
        ->assertSet('fixedAsset.book_value', 35500.0);
});

test('saves changes to the fixed asset', function (): void {
    Livewire::test(FixedAsset::class, ['id' => $this->fixedAsset->getKey()])
        ->set('fixedAsset.name', 'Renamed van')
        ->call('save')
        ->assertHasNoErrors()
        ->assertReturned(true);

    expect($this->fixedAsset->refresh()->name)->toBe('Renamed van');
});

test('marks the months depreciated elsewhere in the schedule', function (): void {
    Livewire::test(FixedAsset::class, ['id' => $this->fixedAsset->getKey()])
        ->set('fixedAsset.externally_depreciated_until', '2026-04-30')
        ->call('save')
        ->assertHasNoErrors();

    $depreciations = Livewire::test(FixedAssetDepreciations::class, ['fixedAssetId' => $this->fixedAsset->getKey()])
        ->call('loadData')
        ->instance()
        ->getDataForTesting();

    expect(data_get($depreciations, 'data.1.status.raw'))->toBe('Booked Elsewhere')
        ->and($this->fixedAsset->refresh()->book_value)->toEqual(35000);
});
