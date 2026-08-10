<?php

use Carbon\Carbon;
use FluxErp\Enums\InstallmentIntervalEnum;
use FluxErp\Enums\RepaymentTypeEnum;
use FluxErp\Support\Calculation\RepaymentScheduleGenerator;

function sumPrincipals(array $schedule): string
{
    return array_reduce(
        $schedule,
        fn (string $carry, array $installment): string => bcadd($carry, $installment['principal_amount'], 2),
        '0'
    );
}

test('annuity schedule keeps the installment constant while interest declines', function (): void {
    $schedule = app(RepaymentScheduleGenerator::class)->generate(
        amount: 12000,
        interestRate: 0.06,
        numberOfInstallments: 12,
        repaymentType: RepaymentTypeEnum::Annuity,
        startsAt: Carbon::parse('2026-01-01'),
    );

    expect($schedule)->toHaveCount(12);

    $payments = array_map(
        fn (array $i): string => bcadd($i['principal_amount'], $i['interest_amount'], 2),
        $schedule
    );

    for ($i = 1; $i < 11; $i++) {
        expect($payments[$i])->toBe($payments[0]);
    }

    for ($i = 1; $i < 12; $i++) {
        expect(bccomp($schedule[$i]['interest_amount'], $schedule[$i - 1]['interest_amount'], 2))
            ->toBe(-1);
    }

    expect(sumPrincipals($schedule))->toBe('12000.00');
});

test('linear schedule keeps the principal constant while the installment declines', function (): void {
    $schedule = app(RepaymentScheduleGenerator::class)->generate(
        amount: 12000,
        interestRate: 0.06,
        numberOfInstallments: 12,
        repaymentType: RepaymentTypeEnum::Linear,
        startsAt: Carbon::parse('2026-01-01'),
    );

    for ($i = 1; $i < 11; $i++) {
        expect($schedule[$i]['principal_amount'])->toBe($schedule[0]['principal_amount']);
    }

    for ($i = 1; $i < 12; $i++) {
        $current = bcadd($schedule[$i]['principal_amount'], $schedule[$i]['interest_amount'], 2);
        $previous = bcadd($schedule[$i - 1]['principal_amount'], $schedule[$i - 1]['interest_amount'], 2);
        expect(bccomp($current, $previous, 2))->toBe(-1);
    }

    expect(sumPrincipals($schedule))->toBe('12000.00');
});

test('the interest rate keeps its decimals instead of being rounded to full percent', function (): void {
    $precise = app(RepaymentScheduleGenerator::class)->generate(
        amount: 12000,
        interestRate: 0.1298,
        numberOfInstallments: 12,
        repaymentType: RepaymentTypeEnum::Annuity,
        startsAt: Carbon::parse('2026-01-01'),
    );

    $rounded = app(RepaymentScheduleGenerator::class)->generate(
        amount: 12000,
        interestRate: 0.12,
        numberOfInstallments: 12,
        repaymentType: RepaymentTypeEnum::Annuity,
        startsAt: Carbon::parse('2026-01-01'),
    );

    expect(bccomp($precise[0]['interest_amount'], $rounded[0]['interest_amount'], 2))->toBe(1)
        ->and($precise[0]['interest_amount'])->toBe('129.80')
        ->and($rounded[0]['interest_amount'])->toBe('120.00')
        ->and(sumPrincipals($precise))->toBe('12000.00');
});

test('zero rate schedule is pure principal', function (): void {
    $schedule = app(RepaymentScheduleGenerator::class)->generate(
        amount: 12000,
        interestRate: 0,
        numberOfInstallments: 12,
        repaymentType: RepaymentTypeEnum::Annuity,
        startsAt: Carbon::parse('2026-01-01'),
    );

    foreach ($schedule as $installment) {
        expect($installment['interest_amount'])->toBe('0.00');
    }

    expect($schedule[0]['principal_amount'])->toBe('1000.00');
    expect(sumPrincipals($schedule))->toBe('12000.00');
});

test('quarterly installments are spaced by three months and carry a quarter of the annual interest', function (): void {
    $schedule = app(RepaymentScheduleGenerator::class)->generate(
        amount: 120000,
        interestRate: 0.0346,
        numberOfInstallments: 20,
        repaymentType: RepaymentTypeEnum::Linear,
        startsAt: Carbon::parse('2026-12-30'),
        interval: InstallmentIntervalEnum::Quarterly,
    );

    expect($schedule)->toHaveCount(20)
        ->and($schedule[0]['due_date'])->toBe('2027-03-30')
        ->and($schedule[1]['due_date'])->toBe('2027-06-30')
        ->and($schedule[19]['due_date'])->toBe('2031-12-30')
        ->and($schedule[0]['interest_amount'])->toBe('1038.00')
        ->and($schedule[0]['principal_amount'])->toBe('6000.00')
        ->and(sumPrincipals($schedule))->toBe('120000.00');
});

test('grace period installments carry interest only and delay the repayment', function (): void {
    $schedule = app(RepaymentScheduleGenerator::class)->generate(
        amount: 120000,
        interestRate: 0.0346,
        numberOfInstallments: 20,
        repaymentType: RepaymentTypeEnum::Linear,
        startsAt: Carbon::parse('2024-12-30'),
        interval: InstallmentIntervalEnum::Quarterly,
        gracePeriodInstallments: 8,
    );

    expect($schedule)->toHaveCount(28);

    for ($i = 0; $i < 8; $i++) {
        expect($schedule[$i]['principal_amount'])->toBe('0.00')
            ->and($schedule[$i]['interest_amount'])->toBe('1038.00');
    }

    expect($schedule[8]['due_date'])->toBe('2027-03-30')
        ->and($schedule[8]['principal_amount'])->toBe('6000.00')
        ->and($schedule[27]['due_date'])->toBe('2031-12-30')
        ->and(sumPrincipals($schedule))->toBe('120000.00');
});

test('a fixed installment amount keeps the payment and leaves the stub on the last installment', function (): void {
    $schedule = app(RepaymentScheduleGenerator::class)->generate(
        amount: 16800,
        interestRate: 0.0275,
        numberOfInstallments: 61,
        repaymentType: RepaymentTypeEnum::Annuity,
        startsAt: Carbon::parse('2021-08-30'),
        installmentAmount: 300,
    );

    expect($schedule)->toHaveCount(61);

    $payment = fn (array $installment): string => bcadd(
        $installment['principal_amount'],
        $installment['interest_amount'],
        2
    );

    for ($i = 0; $i < 60; $i++) {
        expect($payment($schedule[$i]))->toBe('300.00');
    }

    expect(bccomp($payment($schedule[60]), '300.00', 2))->toBe(-1)
        ->and(sumPrincipals($schedule))->toBe('16800.00');
});

test('a fixed installment amount stops once the loan is repaid', function (): void {
    $schedule = app(RepaymentScheduleGenerator::class)->generate(
        amount: 1200,
        interestRate: 0,
        numberOfInstallments: 24,
        repaymentType: RepaymentTypeEnum::Annuity,
        startsAt: Carbon::parse('2026-01-01'),
        installmentAmount: 200,
    );

    expect($schedule)->toHaveCount(6)
        ->and(sumPrincipals($schedule))->toBe('1200.00');
});

test('a fixed installment amount below the interest is rejected', function (): void {
    app(RepaymentScheduleGenerator::class)->generate(
        amount: 120000,
        interestRate: 0.0346,
        numberOfInstallments: 20,
        repaymentType: RepaymentTypeEnum::Annuity,
        startsAt: Carbon::parse('2026-01-01'),
        installmentAmount: 100,
    );
})->throws(InvalidArgumentException::class);
