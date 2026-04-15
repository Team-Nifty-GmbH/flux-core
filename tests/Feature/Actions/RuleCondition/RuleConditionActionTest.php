<?php

use FluxErp\Actions\RuleCondition\CreateRuleCondition;
use FluxErp\Actions\RuleCondition\DeleteRuleCondition;
use FluxErp\Actions\RuleCondition\UpdateRuleCondition;
use FluxErp\Models\Rule;
use FluxErp\Models\RuleCondition;

test('create rule condition', function (): void {
    $rule = Rule::factory()->create();

    $condition = CreateRuleCondition::make([
        'rule_id' => $rule->getKey(),
        'type' => 'or_container',
        'position' => 0,
    ])->validate()->execute();

    expect($condition)
        ->toBeInstanceOf(RuleCondition::class)
        ->type->toBe('or_container')
        ->rule_id->toBe($rule->getKey());
});

test('create rule condition with parent', function (): void {
    $rule = Rule::factory()->create();
    $parent = RuleCondition::factory()->create([
        'rule_id' => $rule->getKey(),
        'type' => 'or_container',
    ]);

    $condition = CreateRuleCondition::make([
        'rule_id' => $rule->getKey(),
        'parent_id' => $parent->getKey(),
        'type' => 'and_container',
        'position' => 0,
    ])->validate()->execute();

    expect($condition->parent_id)->toBe($parent->getKey());
});

test('create rule condition with value', function (): void {
    $rule = Rule::factory()->create();
    $parent = RuleCondition::factory()->create([
        'rule_id' => $rule->getKey(),
        'type' => 'and_container',
    ]);

    $condition = CreateRuleCondition::make([
        'rule_id' => $rule->getKey(),
        'parent_id' => $parent->getKey(),
        'type' => 'date_range',
        'value' => ['from' => '2026-06-01', 'to' => '2026-08-31'],
        'position' => 0,
    ])->validate()->execute();

    expect($condition->value)->toEqual(['from' => '2026-06-01', 'to' => '2026-08-31']);
});

test('create rule condition requires rule_id and type', function (): void {
    CreateRuleCondition::assertValidationErrors([], ['rule_id', 'type']);
});

test('update rule condition', function (): void {
    $rule = Rule::factory()->create();
    $condition = RuleCondition::factory()->create([
        'rule_id' => $rule->getKey(),
        'type' => 'date_range',
        'value' => ['from' => '2026-01-01', 'to' => '2026-12-31'],
    ]);

    $updated = UpdateRuleCondition::make([
        'id' => $condition->getKey(),
        'value' => ['from' => '2026-06-01', 'to' => '2026-08-31'],
    ])->validate()->execute();

    expect($updated->value)->toEqual(['from' => '2026-06-01', 'to' => '2026-08-31']);
});

test('delete rule condition', function (): void {
    $rule = Rule::factory()->create();
    $condition = RuleCondition::factory()->create([
        'rule_id' => $rule->getKey(),
    ]);

    $result = DeleteRuleCondition::make(['id' => $condition->getKey()])
        ->validate()->execute();

    expect($result)->toBeTrue();
    expect(RuleCondition::query()->whereKey($condition->getKey())->exists())->toBeFalse();
});
