<?php

use FluxErp\Models\RebateAgreement;

function agreementWithTiers(array $tiers): RebateAgreement
{
    return app(RebateAgreement::class)->forceFill(['tiers' => $tiers]);
}

test('resolves the percentage of the highest reached tier', function (string $volume, ?string $expected): void {
    $agreement = agreementWithTiers([
        ['from_volume' => 50000, 'percentage' => 0.02],
        ['from_volume' => 100000, 'percentage' => 0.03],
    ]);

    expect($agreement->resolvePercentage($volume))->toBe($expected);
})->with([
    'below the lowest tier' => ['49999.99', null],
    'exactly on the lowest tier' => ['50000', '0.02'],
    'between tiers' => ['99999.99', '0.02'],
    'exactly on the highest tier' => ['100000', '0.03'],
    'above the highest tier' => ['120000', '0.03'],
    'zero volume' => ['0', null],
    'negative volume' => ['-5000', null],
]);

test('resolves the highest tier regardless of the order the tiers are stored in', function (): void {
    $agreement = agreementWithTiers([
        ['from_volume' => 100000, 'percentage' => 0.03],
        ['from_volume' => 50000, 'percentage' => 0.02],
    ]);

    expect($agreement->resolvePercentage('120000'))->toBe('0.03');
});

test('resolves no percentage without tiers', function (): void {
    expect(agreementWithTiers([])->resolvePercentage('120000'))->toBeNull();
});
