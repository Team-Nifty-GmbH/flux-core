<?php

use FluxErp\Traits\CalculatesPositionAvailability;

function calculator(): object
{
    return new class()
    {
        use CalculatesPositionAvailability;

        public function calculate(array $positions, int $multiplier): array
        {
            return $this->calculateMaxAmounts($positions, $multiplier);
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

// Case 1: Order with 2 Anzahlungsrechnungen → Retoure on Order
// Anzahlungen cover 100% of the order amount, so nothing remains for Retoure
test('retoure on order with two full anzahlungsrechnungen has zero available', function (): void {
    $positions = [
        position(1, null, '10'),   // Order position: 10 units
        position(2, 1, '5'),       // Anzahlung 1: 5 units (split-order, multiplier=1, signed=+5)
        position(3, 1, '5'),       // Anzahlung 2: 5 units (split-order, multiplier=1, signed=+5)
    ];

    $result = calculator()->calculate($positions, -1); // Retoure multiplier = -1

    $orderPosition = array_find($result, fn (array $v) => $v['id'] === 1);
    expect(bccomp($orderPosition['signed_amount'], '0'))->toBe(0);
});

// Case 2: Order with 2 Anzahlungsrechnungen, AZ1 has a Retoure → Retoure on Order
// AZ1 was retoured so its amount is freed. Available = Order - AZ2 = 10 - 5 = 5
test('retoure on order where one anzahlung was retoured frees up that amount', function (): void {
    $positions = [
        position(1, null, '10'),   // Order position: 10 units
        position(2, 1, '5'),       // Anzahlung 1: 5 units (signed=+5)
        position(3, 1, '5'),       // Anzahlung 2: 5 units (signed=+5)
        position(4, 2, '-5'),      // Retoure on AZ1: 5 units returned (retoure, multiplier=-1, signed=-5)
    ];

    $result = calculator()->calculate($positions, -1); // Retoure multiplier = -1

    $orderPosition = array_find($result, fn (array $v) => $v['id'] === 1);
    expect(bccomp($orderPosition['signed_amount'], '5'))->toBe(0);
});

// Case 3: Same as Case 2 - Retoure on Schlussrechnung should match the remaining Anzahlung amount
test('retoure on schlussrechnung after retoured anzahlung matches remaining anzahlung', function (): void {
    $positions = [
        position(1, null, '10'),   // Order position: 10 units
        position(2, 1, '5'),       // Anzahlung 1: 5 units (signed=+5)
        position(3, 1, '5'),       // Anzahlung 2: 5 units (signed=+5)
        position(4, 2, '-5'),      // Retoure on AZ1 (signed=-5)
    ];

    $result = calculator()->calculate($positions, -1);

    // Available should be 5 (= 2nd Anzahlung amount)
    $orderPosition = array_find($result, fn (array $v) => $v['id'] === 1);
    expect(bccomp($orderPosition['signed_amount'], '5'))->toBe(0);
});

// Edge case: Order with partial Anzahlungsrechnungen → Retoure
// AZ1=3, AZ2=3 out of 10 → 4 remaining for Retoure
test('retoure on order with partial anzahlungsrechnungen shows correct remainder', function (): void {
    $positions = [
        position(1, null, '10'),   // Order position: 10 units
        position(2, 1, '3'),       // Anzahlung 1: 3 units
        position(3, 1, '3'),       // Anzahlung 2: 3 units
    ];

    $result = calculator()->calculate($positions, -1);

    $orderPosition = array_find($result, fn (array $v) => $v['id'] === 1);
    expect(bccomp($orderPosition['signed_amount'], '4'))->toBe(0);
});

// Edge case: Order with existing Retoure → second Retoure
// Order=10, existing Retoure=3 → 7 remaining for new Retoure
test('second retoure on order with existing retoure shows correct remainder', function (): void {
    $positions = [
        position(1, null, '10'),   // Order position: 10 units
        position(2, 1, '-3'),      // Existing Retoure: 3 returned (signed=-3)
    ];

    $result = calculator()->calculate($positions, -1);

    $orderPosition = array_find($result, fn (array $v) => $v['id'] === 1);
    expect(bccomp($orderPosition['signed_amount'], '7'))->toBe(0);
});

// Multiple positions: each should be calculated independently
test('multiple order positions are calculated independently', function (): void {
    $positions = [
        position(1, null, '10'),   // Position A: 10 units
        position(2, null, '20'),   // Position B: 20 units
        position(3, 1, '5'),       // AZ for Position A: 5 units
        position(4, 2, '10'),      // AZ for Position B: 10 units
    ];

    $result = calculator()->calculate($positions, -1);

    $posA = array_find($result, fn (array $v) => $v['id'] === 1);
    $posB = array_find($result, fn (array $v) => $v['id'] === 2);
    expect(bccomp($posA['signed_amount'], '5'))->toBe(0)
        ->and(bccomp($posB['signed_amount'], '10'))->toBe(0);
});

// Anzahlung creation (multiplier=1): existing Anzahlungen reduce, Retoures don't
test('anzahlung creation correctly reduces by existing anzahlungen', function (): void {
    $positions = [
        position(1, null, '10'),   // Order position: 10 units
        position(2, 1, '5'),       // Existing Anzahlung 1: 5 units
    ];

    $result = calculator()->calculate($positions, 1); // Anzahlung multiplier = 1

    $orderPosition = array_find($result, fn (array $v) => $v['id'] === 1);
    expect(bccomp($orderPosition['signed_amount'], '5'))->toBe(0);
});
