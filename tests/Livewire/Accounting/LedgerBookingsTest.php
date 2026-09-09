<?php

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
