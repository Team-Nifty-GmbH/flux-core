<?php

use FluxErp\Traits\CalculatesPositionAvailability;

function calculator(): object
{
    return new class()
    {
        use CalculatesPositionAvailability;

        public function calculate(array $positions): array
        {
            return $this->calculateMaxAmounts($positions);
        }
    };
}

function position(int $id, ?int $originId, string $signedAmount): object
{
    return (object) [
        'id' => $id,
        'origin_position_id' => $originId,
        'signed_amount' => $signedAmount,
    ];
}

// Order with 2 Anzahlungsrechnungen → Retoure on Order
// Anzahlungen cover 100% of the order amount, so nothing remains for Retoure
test('retoure on order with two full anzahlungsrechnungen has zero available', function (): void {
    $positions = [
        position(1, null, '10'),
        position(2, 1, '5'),
        position(3, 1, '5'),
    ];

    $result = calculator()->calculate($positions);

    $orderPosition = array_find($result, fn (array $v) => $v['id'] === 1);
    expect(bccomp($orderPosition['signed_amount'], '0'))->toBe(0);
});

// Order with 2 AZ, AZ1 has a Retoure → Retoure on Order
// AZ1 was retoured so its amount is freed. Available = Order - AZ2 = 10 - 5 = 5
test('retoure on order where one anzahlung was retoured frees up that amount', function (): void {
    $positions = [
        position(1, null, '10'),
        position(2, 1, '5'),
        position(3, 1, '5'),
        position(4, 2, '-5'),
    ];

    $result = calculator()->calculate($positions);

    $orderPosition = array_find($result, fn (array $v) => $v['id'] === 1);
    expect(bccomp($orderPosition['signed_amount'], '5'))->toBe(0);
});

// Order with partial Anzahlungsrechnungen → Retoure
// AZ1=3, AZ2=3 out of 10 → 4 remaining
test('retoure on order with partial anzahlungsrechnungen shows correct remainder', function (): void {
    $positions = [
        position(1, null, '10'),
        position(2, 1, '3'),
        position(3, 1, '3'),
    ];

    $result = calculator()->calculate($positions);

    $orderPosition = array_find($result, fn (array $v) => $v['id'] === 1);
    expect(bccomp($orderPosition['signed_amount'], '4'))->toBe(0);
});

// Order with existing Retoure → second Retoure
// Order=10, existing Retoure=3 → 7 remaining
test('second retoure on order with existing retoure shows correct remainder', function (): void {
    $positions = [
        position(1, null, '10'),
        position(2, 1, '-3'),
    ];

    $result = calculator()->calculate($positions);

    $orderPosition = array_find($result, fn (array $v) => $v['id'] === 1);
    expect(bccomp($orderPosition['signed_amount'], '7'))->toBe(0);
});

// Multiple positions: each should be calculated independently
test('multiple order positions are calculated independently', function (): void {
    $positions = [
        position(1, null, '10'),
        position(2, null, '20'),
        position(3, 1, '5'),
        position(4, 2, '10'),
    ];

    $result = calculator()->calculate($positions);

    $posA = array_find($result, fn (array $v) => $v['id'] === 1);
    $posB = array_find($result, fn (array $v) => $v['id'] === 2);
    expect(bccomp($posA['signed_amount'], '5'))->toBe(0)
        ->and(bccomp($posB['signed_amount'], '10'))->toBe(0);
});

// Existing Anzahlungen reduce available amount for new Anzahlung
test('anzahlung creation correctly reduces by existing anzahlungen', function (): void {
    $positions = [
        position(1, null, '10'),
        position(2, 1, '5'),
    ];

    $result = calculator()->calculate($positions);

    $orderPosition = array_find($result, fn (array $v) => $v['id'] === 1);
    expect(bccomp($orderPosition['signed_amount'], '5'))->toBe(0);
});

// Retoure on parent (Anzahlung): AZ=5, existing partial Retoure=-3 → 2 remaining
test('retoure on anzahlung with existing partial retoure shows correct remainder', function (): void {
    $positions = [
        position(2, null, '5'),
        position(4, 2, '-3'),
    ];

    $result = calculator()->calculate($positions);

    $azPosition = array_find($result, fn (array $v) => $v['id'] === 2);
    expect(bccomp($azPosition['signed_amount'], '2'))->toBe(0);
});

// Retoure on parent (Anzahlung): fully retoured AZ has zero available
test('retoure on fully retoured anzahlung has zero available', function (): void {
    $positions = [
        position(2, null, '5'),
        position(4, 2, '-5'),
    ];

    $result = calculator()->calculate($positions);

    $azPosition = array_find($result, fn (array $v) => $v['id'] === 2);
    expect(bccomp($azPosition['signed_amount'], '0'))->toBe(0);
});
