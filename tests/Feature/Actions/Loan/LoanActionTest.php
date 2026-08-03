<?php

use FluxErp\Actions\Loan\CreateLoan;
use FluxErp\Actions\Loan\DeleteLoan;
use FluxErp\Actions\Loan\UpdateLoan;
use FluxErp\Enums\RepaymentTypeEnum;
use FluxErp\Models\Contact;
use FluxErp\Models\LedgerAccount;
use FluxErp\Models\Tenant;
use Illuminate\Validation\ValidationException;

beforeEach(function (): void {
    $this->contact = Contact::factory()
        ->hasAttached($this->dbTenant, relationship: 'tenants')
        ->create();
    $this->ledgerAccount = LedgerAccount::factory()->create(['tenant_id' => $this->dbTenant->getKey()]);
});

function baseLoanData(array $overrides = []): array
{
    return array_merge([
        'contact_id' => test()->contact->getKey(),
        'ledger_account_id' => test()->ledgerAccount->getKey(),
        'name' => 'Machine financing',
        'amount' => 12000,
        'interest_rate' => 0.06,
        'repayment_type_enum' => RepaymentTypeEnum::Annuity->value,
        'number_of_installments' => 12,
        'starts_at' => '2026-01-01',
    ], $overrides);
}

test('create loan persists the remaining principal and the total interest', function (): void {
    $loan = CreateLoan::make(baseLoanData())->validate()->execute();

    $this->assertDatabaseHas('loans', [
        'id' => $loan->getKey(),
        'remaining' => 12000,
    ]);

    expect($loan->refresh()->total_interest)
        ->toEqual($loan->installments()->sum('interest_amount'))
        ->and((float) $loan->total_interest)->toBeGreaterThan(0);
});

test('create loan generates the full repayment schedule', function (): void {
    $loan = CreateLoan::make(baseLoanData())->validate()->execute();

    expect($loan->installments()->count())->toBe(12);
    expect($loan->remaining)->toEqual(12000);
    expect($loan->ends_at->toDateString())->toBe('2027-01-01');

    $this->assertDatabaseCount('loan_installments', 12);
});

test('create loan rejects a foreign tenant contact', function (): void {
    $otherTenant = Tenant::factory()->create();
    $foreignContact = Contact::factory()
        ->hasAttached($otherTenant, relationship: 'tenants')
        ->create();

    CreateLoan::make(baseLoanData(['contact_id' => $foreignContact->getKey()]))
        ->validate()
        ->execute();
})->throws(ValidationException::class);

test('remaining drops after an installment is settled', function (): void {
    $loan = CreateLoan::make(baseLoanData(['interest_rate' => 0]))->validate()->execute();

    expect($loan->remaining)->toEqual(12000)
        ->and((float) $loan->progress)->toEqual(0.0);

    $loan->installments()->orderBy('sequence')->first()->update(['is_paid' => true]);

    // one of twelve installments repaid
    expect($loan->refresh()->remaining)->toEqual(11000)
        ->and(round((float) $loan->progress, 4))->toEqual(0.0833);
});

test('update loan', function (): void {
    $loan = CreateLoan::make(baseLoanData())->validate()->execute();

    $updated = UpdateLoan::make([
        'id' => $loan->getKey(),
        'name' => 'Renamed loan',
    ])->validate()->execute();

    expect($updated->name)->toBe('Renamed loan');
    $this->assertDatabaseHas('loans', ['id' => $loan->getKey(), 'name' => 'Renamed loan']);
});

test('update loan leaves schedule affecting fields locked', function (): void {
    $loan = CreateLoan::make(baseLoanData())->validate()->execute();

    $updated = UpdateLoan::make([
        'id' => $loan->getKey(),
        'amount' => 999,
        'interest_rate' => 0.5,
        'number_of_installments' => 3,
    ])->validate()->execute();

    expect((string) $updated->amount)->toBe((string) $loan->amount)
        ->and($updated->number_of_installments)->toBe($loan->number_of_installments);
});

test('update loan rejects a foreign tenant contact', function (): void {
    $loan = CreateLoan::make(baseLoanData())->validate()->execute();

    $otherTenant = Tenant::factory()->create();
    $foreignContact = Contact::factory()
        ->hasAttached($otherTenant, relationship: 'tenants')
        ->create();

    UpdateLoan::make([
        'id' => $loan->getKey(),
        'contact_id' => $foreignContact->getKey(),
    ])->validate()->execute();
})->throws(ValidationException::class);

test('delete loan soft deletes', function (): void {
    $loan = CreateLoan::make(baseLoanData())->validate()->execute();

    DeleteLoan::make(['id' => $loan->getKey()])->validate()->execute();

    $this->assertSoftDeleted('loans', ['id' => $loan->getKey()]);
});

test('deleting an installment updates every derived column', function (): void {
    $loan = CreateLoan::make(baseLoanData(['number_of_installments' => 2, 'interest_rate' => 0.12]))
        ->validate()
        ->execute();

    $lastInstallment = $loan->installments()->orderByDesc('sequence')->first();
    $expectedInterest = bcsub((string) $loan->total_interest, (string) $lastInstallment->interest_amount, 2);

    $lastInstallment->delete();

    expect($loan->refresh()->total_interest)->toEqual($expectedInterest)
        ->and($loan->remaining)->toEqual($loan->installments()->sum('principal_amount'));
});
