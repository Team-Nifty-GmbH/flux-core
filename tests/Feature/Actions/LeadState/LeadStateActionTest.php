<?php

use FluxErp\Actions\LeadState\CreateLeadState;
use FluxErp\Actions\LeadState\DeleteLeadState;
use FluxErp\Actions\LeadState\UpdateLeadState;
use FluxErp\Models\LeadState;

test('create lead state', function (): void {
    $state = CreateLeadState::make([
        'name' => 'Qualified',
        'probability_percentage' => 0.5,
    ])->validate()->execute();

    expect($state)->toBeInstanceOf(LeadState::class)
        ->name->toBe('Qualified');
});

test('create lead state requires name', function (): void {
    CreateLeadState::assertValidationErrors([], 'name');
});

test('is_won and is_lost are mutually exclusive', function (): void {
    CreateLeadState::assertValidationErrors(
        ['name' => 'Invalid', 'is_won' => true, 'is_lost' => true],
        ['is_won', 'is_lost']
    );
});

test('update lead state', function (): void {
    $state = LeadState::factory()->create();

    $updated = UpdateLeadState::make([
        'id' => $state->getKey(),
        'name' => 'Won',
        'is_won' => true,
        'is_default' => false,
        'is_lost' => false,
    ])->validate()->execute();

    expect($updated)->name->toBe('Won');
});

test('delete lead state', function (): void {
    $state = LeadState::factory()->create();

    expect(DeleteLeadState::make(['id' => $state->getKey()])
        ->validate()->execute())->toBeTrue();
});
