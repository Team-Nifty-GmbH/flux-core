<?php

use FluxErp\Actions\LedgerBooking\CreateLedgerBooking;
use FluxErp\Actions\LedgerBooking\DeleteLedgerBooking;
use FluxErp\Actions\LedgerBooking\UpdateLedgerBooking;
use FluxErp\Enums\LedgerAccountTypeEnum;
use FluxErp\Models\LedgerAccount;
use FluxErp\Models\LedgerBooking;
use FluxErp\Models\Tenant;
use Illuminate\Validation\ValidationException;

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

test('create ledger booking', function (): void {
    $booking = CreateLedgerBooking::make([
        'tenant_id' => $this->dbTenant->getKey(),
        'debit_ledger_account_id' => $this->debitAccount->getKey(),
        'credit_ledger_account_id' => $this->creditAccount->getKey(),
        'amount' => 52320,
        'booking_date' => '2026-07-01',
        'booking_text' => 'Umbuchung Kreditor an Darlehen',
    ])
        ->validate()
        ->execute();

    expect($booking)->toBeInstanceOf(LedgerBooking::class)
        ->and($booking->debit_ledger_account_id)->toBe($this->debitAccount->getKey())
        ->and($booking->credit_ledger_account_id)->toBe($this->creditAccount->getKey())
        ->and((float) $booking->amount)->toBe(52320.0);
});

test('create ledger booking rejects debit equal to credit', function (): void {
    CreateLedgerBooking::assertValidationErrors([
        'tenant_id' => $this->dbTenant->getKey(),
        'debit_ledger_account_id' => $this->debitAccount->getKey(),
        'credit_ledger_account_id' => $this->debitAccount->getKey(),
        'amount' => 100,
        'booking_date' => '2026-07-01',
    ], 'credit_ledger_account_id');
});

test('create ledger booking rejects a foreign tenant account', function (): void {
    $foreignTenant = Tenant::factory()->create();
    $foreignAccount = LedgerAccount::factory()->create([
        'tenant_id' => $foreignTenant->getKey(),
        'ledger_account_type_enum' => LedgerAccountTypeEnum::Liability,
    ]);

    try {
        CreateLedgerBooking::make([
            'tenant_id' => $this->dbTenant->getKey(),
            'debit_ledger_account_id' => $this->debitAccount->getKey(),
            'credit_ledger_account_id' => $foreignAccount->getKey(),
            'amount' => 100,
            'booking_date' => '2026-07-01',
        ])
            ->validate()
            ->execute();

        $this->fail('Expected the foreign tenant account to be rejected.');
    } catch (ValidationException $e) {
        expect($e->errors())->toHaveKey('debit_ledger_account_id');
    }
});

test('update ledger booking', function (): void {
    $booking = LedgerBooking::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'debit_ledger_account_id' => $this->debitAccount->getKey(),
        'credit_ledger_account_id' => $this->creditAccount->getKey(),
    ]);

    $updated = UpdateLedgerBooking::make([
        'id' => $booking->getKey(),
        'amount' => 999,
    ])
        ->validate()
        ->execute();

    expect((float) $updated->amount)->toBe(999.0);
});

test('delete ledger booking', function (): void {
    $booking = LedgerBooking::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'debit_ledger_account_id' => $this->debitAccount->getKey(),
        'credit_ledger_account_id' => $this->creditAccount->getKey(),
    ]);

    expect(DeleteLedgerBooking::make(['id' => $booking->getKey()])->validate()->execute())->toBeTrue();
    $this->assertSoftDeleted('ledger_bookings', ['id' => $booking->getKey()]);
});

test('ledger account booking balance is debits minus credits', function (): void {
    LedgerBooking::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'debit_ledger_account_id' => $this->debitAccount->getKey(),
        'credit_ledger_account_id' => $this->creditAccount->getKey(),
        'amount' => 1000,
    ]);
    LedgerBooking::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'debit_ledger_account_id' => $this->creditAccount->getKey(),
        'credit_ledger_account_id' => $this->debitAccount->getKey(),
        'amount' => 400,
    ]);

    expect($this->debitAccount->calculateBookingBalance())->toBe('600.00')
        ->and($this->creditAccount->calculateBookingBalance())->toBe('-600.00');
});
