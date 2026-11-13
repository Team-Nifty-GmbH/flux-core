<?php

use FluxErp\Actions\FixedAsset\BookFixedAssetDepreciations;
use FluxErp\Actions\FixedAsset\CreateFixedAsset;
use FluxErp\Enums\LedgerAccountTypeEnum;
use FluxErp\Livewire\Accounting\LedgerBookings;
use FluxErp\Models\LedgerAccount;
use FluxErp\Models\LedgerBooking;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->debitAccount = LedgerAccount::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'ledger_account_type_enum' => LedgerAccountTypeEnum::Asset,
    ]);
    $this->creditAccount = LedgerAccount::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'ledger_account_type_enum' => LedgerAccountTypeEnum::Liability,
    ]);
});

test('renders successfully', function (): void {
    Livewire::test(LedgerBookings::class)
        ->assertOk();
});

test('edit with null resets form and opens modal', function (): void {
    Livewire::test(LedgerBookings::class)
        ->call('edit')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('ledgerBooking.id', null)
        ->assertSet('ledgerBooking.amount', null)
        ->assertOpensModal('edit-ledger-booking-modal');
});

test('can create a ledger booking', function (): void {
    Livewire::test(LedgerBookings::class)
        ->call('edit')
        ->set('ledgerBooking.debit_ledger_account_id', $this->debitAccount->getKey())
        ->set('ledgerBooking.credit_ledger_account_id', $this->creditAccount->getKey())
        ->set('ledgerBooking.amount', 52320)
        ->set('ledgerBooking.booking_date', '2026-07-01')
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors();

    $this->assertDatabaseHas('ledger_bookings', [
        'debit_ledger_account_id' => $this->debitAccount->getKey(),
        'credit_ledger_account_id' => $this->creditAccount->getKey(),
    ]);
});

test('can delete a ledger booking', function (): void {
    $booking = LedgerBooking::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'debit_ledger_account_id' => $this->debitAccount->getKey(),
        'credit_ledger_account_id' => $this->creditAccount->getKey(),
    ]);

    Livewire::test(LedgerBookings::class)
        ->call('delete', $booking->getKey())
        ->assertOk()
        ->assertHasNoErrors();

    $this->assertSoftDeleted('ledger_bookings', ['id' => $booking->getKey()]);
});

test('depreciation bookings are marked and cannot be deleted from the page', function (): void {
    $fixedAsset = CreateFixedAsset::make([
        'ledger_account_id' => $this->debitAccount->getKey(),
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
    BookFixedAssetDepreciations::make(['fixed_asset_id' => $fixedAsset->getKey(), 'until' => '2026-03-31'])
        ->validate()
        ->execute();
    $booking = LedgerBooking::query()->sole();

    $component = Livewire::test(LedgerBookings::class)
        ->call('loadData')
        ->assertOk();

    expect(data_get($component->instance()->getDataForTesting(), 'data.0.is_depreciation'))->toBeTrue();

    $component->call('delete', $booking->getKey())
        ->assertReturned(false);

    expect($booking->refresh()->trashed())->toBeFalse();
});
