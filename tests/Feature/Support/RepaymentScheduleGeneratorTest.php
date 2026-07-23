<?php

use Carbon\Carbon;
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

test('annuity schedule keeps the instalment constant while interest declines', function (): void {
    $schedule = app(RepaymentScheduleGenerator::class)->generate(
        amount: 12000,
        interestRate: 0.06,
        numberOfInstallments: 12,
        repaymentType: RepaymentTypeEnum::Annuity,
        startsAt: Carbon::parse('2026-01-01'),
    );

    expect($schedule)->toHaveCount(12);

    // Instalment (principal + interest) is constant, except the last which
    // absorbs the rounding remainder.
    $payments = array_map(
        fn (array $i): string => bcadd($i['principal_amount'], $i['interest_amount'], 2),
        $schedule
    );

    for ($i = 1; $i < 11; $i++) {
        expect($payments[$i])->toBe($payments[0]);
    }

    // Interest strictly declines on the falling balance.
    for ($i = 1; $i < 12; $i++) {
        expect(bccomp($schedule[$i]['interest_amount'], $schedule[$i - 1]['interest_amount'], 2))
            ->toBe(-1);
    }

    expect(sumPrincipals($schedule))->toBe('12000.00');
});

test('linear schedule keeps the principal constant while the instalment declines', function (): void {
    $schedule = app(RepaymentScheduleGenerator::class)->generate(
        amount: 12000,
        interestRate: 0.06,
        numberOfInstallments: 12,
        repaymentType: RepaymentTypeEnum::Linear,
        startsAt: Carbon::parse('2026-01-01'),
    );

    // Constant principal on every instalment but the last.
    for ($i = 1; $i < 11; $i++) {
        expect($schedule[$i]['principal_amount'])->toBe($schedule[0]['principal_amount']);
    }

    // Total instalment declines as interest falls with the balance.
    for ($i = 1; $i < 12; $i++) {
        $current = bcadd($schedule[$i]['principal_amount'], $schedule[$i]['interest_amount'], 2);
        $previous = bcadd($schedule[$i - 1]['principal_amount'], $schedule[$i - 1]['interest_amount'], 2);
        expect(bccomp($current, $previous, 2))->toBe(-1);
    }

    expect(sumPrincipals($schedule))->toBe('12000.00');
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
