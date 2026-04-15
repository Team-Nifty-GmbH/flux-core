<?php

use FluxErp\Livewire\RuleConditionBuilder;
use FluxErp\Models\Rule;
use Livewire\Livewire;

test('rule condition builder renders', function (): void {
    $rule = Rule::factory()->create();

    Livewire::test(RuleConditionBuilder::class, ['ruleId' => $rule->getKey()])
        ->assertOk();
});

test('can add or group', function (): void {
    $rule = Rule::factory()->create();

    Livewire::test(RuleConditionBuilder::class, ['ruleId' => $rule->getKey()])
        ->call('addOrGroup')
        ->assertOk();

    expect($rule->conditions()->where('type', 'or_container')->exists())->toBeTrue();
    expect($rule->conditions()->where('type', 'and_container')->exists())->toBeTrue();
});

test('can add condition to and group', function (): void {
    $rule = Rule::factory()->create();

    $component = Livewire::test(RuleConditionBuilder::class, ['ruleId' => $rule->getKey()])
        ->call('addOrGroup');

    $andContainer = $rule->conditions()->where('type', 'and_container')->first();

    $component->call('addCondition', $andContainer->getKey(), 'date_range')
        ->assertOk();

    expect($rule->conditions()->where('type', 'date_range')->exists())->toBeTrue();
});
