<?php

use FluxErp\Actions\Loan\CreateLoan;
use FluxErp\Actions\LoanExtraRepayment\CreateLoanExtraRepayment;
use FluxErp\Enums\InstallmentIntervalEnum;
use FluxErp\Enums\RepaymentTypeEnum;
use FluxErp\Enums\ScheduleAdjustmentTypeEnum;
use FluxErp\Models\Contact;
use FluxErp\Models\LedgerAccount;
use Illuminate\Validation\ValidationException;

beforeEach(function (): void {
    $this->contact = Contact::factory()
        ->hasAttached($this->dbTenant, relationship: 'tenants')
        ->create();
    $this->ledgerAccount = LedgerAccount::factory()->create(['tenant_id' => $this->dbTenant->getKey()]);
});

function loanFor(array $overrides = [])
{
    return CreateLoan::make(array_merge([
        'contact_id' => test()->contact->getKey(),
        'ledger_account_id' => test()->ledgerAccount->getKey(),
        'name' => 'Machine financing',
        'amount' => 12000,
        'interest_rate' => 0.06,
        'repayment_type_enum' => RepaymentTypeEnum::Annuity->value,
        'number_of_installments' => 12,
        'starts_at' => '2026-01-01',
    ], $overrides))->validate()->execute();
}

function extraRepaymentFor($loan, array $overrides = [])
{
    return CreateLoanExtraRepayment::make(array_merge([
        'loan_id' => $loan->getKey(),
        'executed_at' => '2026-02-15',
        'amount' => 3000,
        'schedule_adjustment_type_enum' => ScheduleAdjustmentTypeEnum::ShortenTerm->value,
    ], $overrides))->validate()->execute();
}

test('an annuity extra repayment keeps the installment and ends the schedule earlier', function (): void {
    $loan = loanFor();
    $installment = $loan->installment_amount;

    $extraRepayment = extraRepaymentFor($loan);
    $loan->refresh();

    $installments = $loan->installments()->orderBy('sequence')->get();

    expect($installments->count())->toBeLessThan(12)
        ->and($loan->remaining)->toEqual(9000)
        ->and($extraRepayment->installments_saved)->toBeGreaterThan(0)
        ->and((float) $extraRepayment->interest_saved)->toBeGreaterThan(0);

    $payment = bcadd($installments->first()->principal_amount, $installments->first()->interest_amount, 2);
    expect((float) $payment)->toEqualWithDelta((float) $installment, 0.01);
});

test('an annuity extra repayment can keep the term and lower the installment instead', function (): void {
    $loan = loanFor();
    $installmentBefore = (float) $loan->installment_amount;

    extraRepaymentFor($loan, [
        'schedule_adjustment_type_enum' => ScheduleAdjustmentTypeEnum::ReduceInstallment->value,
    ]);
    $loan->refresh();

    expect($loan->installments()->count())->toBe(12)
        ->and((float) $loan->installment_amount)->toBeLessThan($installmentBefore)
        ->and($loan->remaining)->toEqual(9000);
});

test('a linear extra repayment keeps the principal share and drops installments', function (): void {
    $loan = loanFor([
        'repayment_type_enum' => RepaymentTypeEnum::Linear->value,
        'amount' => 12000,
        'number_of_installments' => 12,
    ]);

    extraRepaymentFor($loan, ['amount' => 3000]);
    $loan->refresh();

    expect($loan->installments()->count())->toBe(9)
        ->and($loan->remaining)->toEqual(9000)
        ->and((float) $loan->installments()->orderBy('sequence')->first()->principal_amount)
        ->toEqualWithDelta(1000.0, 0.01);
});

test('a linear extra repayment can lower the principal share instead', function (): void {
    $loan = loanFor([
        'repayment_type_enum' => RepaymentTypeEnum::Linear->value,
    ]);

    extraRepaymentFor($loan, [
        'amount' => 3000,
        'schedule_adjustment_type_enum' => ScheduleAdjustmentTypeEnum::ReduceInstallment->value,
    ]);
    $loan->refresh();

    expect($loan->installments()->count())->toBe(12)
        ->and((float) $loan->installments()->orderBy('sequence')->first()->principal_amount)
        ->toEqualWithDelta(750.0, 0.01);
});

test('an extra repayment during the grace period keeps the interest only installments', function (): void {
    $loan = loanFor([
        'repayment_type_enum' => RepaymentTypeEnum::Linear->value,
        'amount' => 120000,
        'interest_rate' => 0.0346,
        'number_of_installments' => 20,
        'installment_interval_enum' => InstallmentIntervalEnum::Quarterly->value,
        'grace_period_installments' => 8,
        'starts_at' => '2024-12-30',
    ]);

    extraRepaymentFor($loan, ['amount' => 24000, 'executed_at' => '2025-06-30']);
    $loan->refresh();

    $installments = $loan->installments()->orderBy('sequence')->get();
    $gracePeriod = $installments->take(8);

    expect($gracePeriod->every(fn ($installment): bool => (float) $installment->principal_amount === 0.0))
        ->toBeTrue()
        ->and((float) $gracePeriod->first()->interest_amount)->toEqualWithDelta(830.4, 0.01)
        ->and($loan->remaining)->toEqual(96000)
        ->and($installments->count())->toBe(24);
});

test('an extra repayment that covers the balance settles the loan', function (): void {
    $loan = loanFor();

    extraRepaymentFor($loan, ['amount' => 12000]);
    $loan->refresh();

    expect($loan->installments()->count())->toBe(0)
        ->and($loan->remaining)->toEqual(0)
        ->and((float) $loan->progress)->toEqual(1.0);
});

test('an extra repayment is rejected when the loan allows none', function (): void {
    $loan = loanFor(['allows_extra_repayments' => false]);

    extraRepaymentFor($loan);
})->throws(ValidationException::class);

test('an extra repayment is rejected beyond the yearly allowance', function (): void {
    $loan = loanFor(['extra_repayment_allowance_percentage' => 0.05]);

    expect($loan->extraRepaymentAllowance())->toBe('600.00');

    extraRepaymentFor($loan, ['amount' => 601]);
})->throws(ValidationException::class);

test('the yearly allowance counts what was already used', function (): void {
    $loan = loanFor(['extra_repayment_allowance_amount' => 1000]);

    extraRepaymentFor($loan, ['amount' => 400, 'executed_at' => '2026-02-15']);
    $loan->refresh();

    expect($loan->remainingExtraRepaymentAllowance(2026))->toBe('600.00')
        ->and($loan->remainingExtraRepaymentAllowance(2027))->toBe('1000.00');

    extraRepaymentFor($loan, ['amount' => 601, 'executed_at' => '2026-06-15']);
})->throws(ValidationException::class);

test('an unlimited allowance takes any amount up to the outstanding principal', function (): void {
    $loan = loanFor();

    expect($loan->extraRepaymentAllowance())->toBeNull()
        ->and($loan->remainingExtraRepaymentAllowance(2026))->toBeNull();

    extraRepaymentFor($loan, ['amount' => 11000]);

    expect($loan->refresh()->remaining)->toEqual(1000);
});

test('an extra repayment beyond the outstanding principal is rejected', function (): void {
    $loan = loanFor();

    extraRepaymentFor($loan, ['amount' => 12000.01]);
})->throws(ValidationException::class);

test('settled installments and their sequence survive the reschedule', function (): void {
    $loan = loanFor();
    $loan->installments()->orderBy('sequence')->limit(2)->update(['is_paid' => true]);

    extraRepaymentFor($loan, ['amount' => 1000]);
    $loan->refresh();

    $installments = $loan->installments()->orderBy('sequence')->get();

    expect($installments->take(2)->every(fn ($installment): bool => $installment->is_paid))->toBeTrue()
        ->and($installments->first()->sequence)->toBe(1)
        ->and($installments->get(2)->sequence)->toBe(3);
});
