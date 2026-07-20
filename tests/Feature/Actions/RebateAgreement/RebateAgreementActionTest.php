<?php

use FluxErp\Actions\RebateAgreement\CreateRebateAgreement;
use FluxErp\Actions\RebateAgreement\DeleteRebateAgreement;
use FluxErp\Actions\RebateAgreement\UpdateRebateAgreement;
use FluxErp\Models\Contact;
use FluxErp\Models\RebateAgreement;

test('create rebate agreement', function (): void {
    $rebateAgreement = CreateRebateAgreement::make([
        'contact_id' => Contact::factory()->create()->getKey(),
        'name' => 'Annual Bonus',
        'period_start' => '2025-01-01',
        'period_end' => '2025-12-31',
        'tiers' => [['from_volume' => 50000, 'percentage' => 0.02]],
    ])
        ->validate()
        ->execute();

    expect($rebateAgreement)->toBeInstanceOf(RebateAgreement::class)
        ->and($rebateAgreement->name)->toBe('Annual Bonus')
        ->and($rebateAgreement->tiers)->toHaveCount(1);
});

test('create rebate agreement requires a contact, a period and tiers', function (): void {
    CreateRebateAgreement::assertValidationErrors([], ['contact_id', 'period_start', 'period_end', 'tiers']);
});

test('the period must end after it starts', function (): void {
    CreateRebateAgreement::assertValidationErrors([
        'contact_id' => Contact::factory()->create()->getKey(),
        'period_start' => '2025-12-31',
        'period_end' => '2025-01-01',
        'tiers' => [['from_volume' => 50000, 'percentage' => 0.02]],
    ], 'period_end');
});

test('a tier percentage above one is rejected', function (): void {
    CreateRebateAgreement::assertValidationErrors([
        'contact_id' => Contact::factory()->create()->getKey(),
        'period_start' => '2025-01-01',
        'period_end' => '2025-12-31',
        'tiers' => [['from_volume' => 50000, 'percentage' => 1.5]],
    ], 'tiers.0.percentage');
});

test('a negative from volume is rejected', function (): void {
    CreateRebateAgreement::assertValidationErrors([
        'contact_id' => Contact::factory()->create()->getKey(),
        'period_start' => '2025-01-01',
        'period_end' => '2025-12-31',
        'tiers' => [['from_volume' => -1, 'percentage' => 0.02]],
    ], 'tiers.0.from_volume');
});

test('duplicate tier thresholds are rejected', function (): void {
    CreateRebateAgreement::assertValidationErrors([
        'contact_id' => Contact::factory()->create()->getKey(),
        'period_start' => '2025-01-01',
        'period_end' => '2025-12-31',
        'tiers' => [
            ['from_volume' => 50000, 'percentage' => 0.02],
            ['from_volume' => 50000, 'percentage' => 0.03],
        ],
    ], 'tiers.0.from_volume');
});

test('update rebate agreement', function (): void {
    $rebateAgreement = RebateAgreement::factory()->create([
        'contact_id' => Contact::factory()->create()->getKey(),
    ]);

    $updated = UpdateRebateAgreement::make([
        'id' => $rebateAgreement->getKey(),
        'name' => 'Renamed',
    ])
        ->validate()
        ->execute();

    expect($updated->name)->toBe('Renamed');
});

test('a settled rebate agreement can neither be updated nor deleted', function (): void {
    $rebateAgreement = RebateAgreement::factory()->create([
        'contact_id' => Contact::factory()->create()->getKey(),
        'settled_at' => now(),
    ]);

    UpdateRebateAgreement::assertValidationErrors(['id' => $rebateAgreement->getKey(), 'name' => 'Renamed'], 'id');
    DeleteRebateAgreement::assertValidationErrors(['id' => $rebateAgreement->getKey()], 'id');
});

test('delete rebate agreement', function (): void {
    $rebateAgreement = RebateAgreement::factory()->create([
        'contact_id' => Contact::factory()->create()->getKey(),
    ]);

    expect(DeleteRebateAgreement::make(['id' => $rebateAgreement->getKey()])->validate()->execute())->toBeTrue();

    $this->assertSoftDeleted('rebate_agreements', ['id' => $rebateAgreement->getKey()]);
});
