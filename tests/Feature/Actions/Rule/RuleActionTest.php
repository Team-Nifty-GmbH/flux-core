<?php

use FluxErp\Actions\Rule\CreateRule;
use FluxErp\Actions\Rule\DeleteRule;
use FluxErp\Actions\Rule\UpdateRule;
use FluxErp\Models\Rule;

test('create rule', function (): void {
    $rule = CreateRule::make([
        'name' => 'Summer Season',
        'description' => 'Active during summer months',
        'priority' => 10,
        'is_active' => true,
    ])->validate()->execute();

    expect($rule)
        ->toBeInstanceOf(Rule::class)
        ->name->toBe('Summer Season')
        ->priority->toBe(10)
        ->is_active->toBeTrue();
});

test('create rule requires name', function (): void {
    CreateRule::assertValidationErrors([], 'name');
});

test('update rule', function (): void {
    $rule = Rule::factory()->create(['name' => 'Old Name']);

    $updated = UpdateRule::make([
        'id' => $rule->getKey(),
        'name' => 'New Name',
        'priority' => 50,
    ])->validate()->execute();

    expect($updated)
        ->name->toBe('New Name')
        ->priority->toBe(50);
});

test('update rule requires id', function (): void {
    UpdateRule::assertValidationErrors(['name' => 'Test'], 'id');
});

test('delete rule', function (): void {
    $rule = Rule::factory()->create();

    $result = DeleteRule::make(['id' => $rule->getKey()])
        ->validate()->execute();

    expect($result)->toBeTrue();
    expect(Rule::query()->whereKey($rule->getKey())->exists())->toBeFalse();
});
