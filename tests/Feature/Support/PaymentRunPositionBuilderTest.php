<?php

use FluxErp\Support\PaymentRunPositionBuilder;

test('a position with many documents never contains a partial document number and stays within 140 characters', function (): void {
    $builder = new PaymentRunPositionBuilder();
    $numbers = collect(range(1, 30))
        ->map(fn (int $i) => 'RE-2026-' . str_pad((string) $i, 6, '0', STR_PAD_LEFT))
        ->all();

    $purpose = $builder->purpose($numbers);

    expect(mb_strlen($purpose))->toBeLessThanOrEqual(140);

    [$list] = explode(' +', $purpose, 2);
    $listedNumbers = array_filter(explode(', ', $list));

    foreach ($listedNumbers as $listedNumber) {
        expect($numbers)->toContain($listedNumber);
    }
});

test('the count of omitted documents is stated when not all fit', function (): void {
    $builder = new PaymentRunPositionBuilder();
    $numbers = collect(range(1, 30))
        ->map(fn (int $i) => 'RE-2026-' . str_pad((string) $i, 6, '0', STR_PAD_LEFT))
        ->all();

    $purpose = $builder->purpose($numbers);

    [$list] = explode(' +', $purpose, 2);
    $listedCount = count(array_filter(explode(', ', $list)));
    $omittedCount = count($numbers) - $listedCount;

    expect($purpose)->toContain('+' . $omittedCount . ' more');
});

test('the reference is included in the tail once it is known', function (): void {
    $builder = new PaymentRunPositionBuilder();
    $numbers = collect(range(1, 30))
        ->map(fn (int $i) => 'RE-2026-' . str_pad((string) $i, 6, '0', STR_PAD_LEFT))
        ->all();

    $purpose = $builder->purpose($numbers, 'PR1-2');

    expect($purpose)->toContain('advice PR1-2')
        ->and(mb_strlen($purpose))->toBeLessThanOrEqual(140);
});

test('a position with few documents produces the plain list with no tail', function (): void {
    $builder = new PaymentRunPositionBuilder();

    $purpose = $builder->purpose(['RE-1', 'RE-2', 'RE-3'], 'PR1-2');

    expect($purpose)->toBe('RE-1, RE-2, RE-3');
});
