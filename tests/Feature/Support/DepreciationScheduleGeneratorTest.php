<?php

use Carbon\Carbon;
use FluxErp\Enums\DepreciationMethodEnum;
use FluxErp\Support\Calculation\DepreciationScheduleGenerator;

function generateDepreciations(
    string|int $acquisitionCost,
    string|int $residualValue,
    int $usefulLifeMonths,
    string $startsAt = '2026-01-01',
): array {
    return app(DepreciationScheduleGenerator::class)->generate(
        acquisitionCost: $acquisitionCost,
        residualValue: $residualValue,
        usefulLifeMonths: $usefulLifeMonths,
        method: DepreciationMethodEnum::Linear,
        startsAt: Carbon::parse($startsAt),
    );
}

function sumDepreciations(array $schedule): string
{
    return array_reduce(
        $schedule,
        fn (string $carry, array $depreciation): string => bcadd($carry, $depreciation['amount'], 2),
        '0'
    );
}

test('linear depreciation rounds half up and puts the remainder on the last month', function (): void {
    $schedule = generateDepreciations(1000, 0, 36);

    expect($schedule)->toHaveCount(36)
        ->and(array_unique(array_column(array_slice($schedule, 0, 35), 'amount')))->toBe(['27.78'])
        ->and(array_last($schedule)['amount'])->toBe('27.70')
        ->and(sumDepreciations($schedule))->toBe('1000.00')
        ->and(array_last($schedule)['book_value_after'])->toBe('0.00');
});

test('linear depreciation stops at the residual value', function (): void {
    $schedule = generateDepreciations(1001, 1, 12);

    expect($schedule)->toHaveCount(12)
        ->and($schedule[0]['amount'])->toBe('83.33')
        ->and($schedule[0]['book_value_after'])->toBe('917.67')
        ->and(sumDepreciations($schedule))->toBe('1000.00')
        ->and(array_last($schedule)['book_value_after'])->toBe('1.00');
});

test('depreciation starts with the full month of a mid year acquisition and books on month end', function (): void {
    $schedule = generateDepreciations(1200, 0, 12, '2026-03-20');

    expect($schedule[0])->toMatchArray(['sequence' => 1, 'booking_date' => '2026-03-31', 'amount' => '100.00'])
        ->and($schedule[1]['booking_date'])->toBe('2026-04-30')
        ->and($schedule[9]['booking_date'])->toBe('2026-12-31')
        ->and(array_last($schedule))->toMatchArray([
            'sequence' => 12,
            'booking_date' => '2027-02-28',
            'book_value_after' => '0.00',
        ]);
});

test('rounding up never depreciates below the residual value', function (): void {
    $schedule = generateDepreciations(1, 0, 40);

    expect(sumDepreciations($schedule))->toBe('1.00')
        ->and(array_last($schedule)['book_value_after'])->toBe('0.00');

    foreach ($schedule as $depreciation) {
        expect(bccomp($depreciation['amount'], '0', 2))->toBe(1);
    }
});

test('a depreciable amount below one cent per month is rejected', function (): void {
    generateDepreciations('0.10', 0, 36);
})->throws(InvalidArgumentException::class);
