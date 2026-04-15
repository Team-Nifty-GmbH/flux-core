<?php

use FluxErp\Models\Rule;
use FluxErp\Models\RuleCondition;
use FluxErp\RuleEngine\ConditionRegistry;
use FluxErp\RuleEngine\Conditions\AndContainerCondition;
use FluxErp\RuleEngine\Conditions\OrContainerCondition;
use FluxErp\RuleEngine\Contracts\ConditionInterface;
use FluxErp\RuleEngine\RuleEvaluator;
use FluxErp\RuleEngine\Scopes\PriceScope;
use FluxErp\RuleEngine\Scopes\RuleScope;

// Test helper conditions — define these INSIDE the test file, before the tests
class AlwaysTrueCondition implements ConditionInterface
{
    public static function type(): string
    {
        return 'always_true';
    }

    public static function label(): string
    {
        return 'Always True';
    }

    public static function group(): string
    {
        return 'test';
    }

    public static function schema(): array
    {
        return [];
    }

    public static function supportedScopes(): array
    {
        return [];
    }

    public function match(RuleScope $scope): bool
    {
        return true;
    }
}

class AlwaysFalseCondition implements ConditionInterface
{
    public static function type(): string
    {
        return 'always_false';
    }

    public static function label(): string
    {
        return 'Always False';
    }

    public static function group(): string
    {
        return 'test';
    }

    public static function schema(): array
    {
        return [];
    }

    public static function supportedScopes(): array
    {
        return [];
    }

    public function match(RuleScope $scope): bool
    {
        return false;
    }
}

beforeEach(function (): void {
    $registry = app(ConditionRegistry::class);
    $registry->register([
        OrContainerCondition::class,
        AndContainerCondition::class,
        AlwaysTrueCondition::class,
        AlwaysFalseCondition::class,
    ]);
});

test('rule with no conditions evaluates to true', function (): void {
    $rule = Rule::factory()->create();
    $result = RuleEvaluator::evaluate($rule, new PriceScope());
    expect($result)->toBeTrue();
});

test('rule with single and group and matching condition evaluates to true', function (): void {
    $rule = Rule::factory()->create();

    $orContainer = RuleCondition::factory()->create([
        'rule_id' => $rule->getKey(),
        'type' => 'or_container',
        'parent_id' => null,
    ]);

    $andContainer = RuleCondition::factory()->create([
        'rule_id' => $rule->getKey(),
        'type' => 'and_container',
        'parent_id' => $orContainer->getKey(),
    ]);

    RuleCondition::factory()->create([
        'rule_id' => $rule->getKey(),
        'type' => 'always_true',
        'parent_id' => $andContainer->getKey(),
        'value' => [],
    ]);

    $result = RuleEvaluator::evaluate($rule, new PriceScope());
    expect($result)->toBeTrue();
});

test('or container returns true if any and group matches', function (): void {
    $rule = Rule::factory()->create();

    $orContainer = RuleCondition::factory()->create([
        'rule_id' => $rule->getKey(),
        'type' => 'or_container',
        'parent_id' => null,
    ]);

    // First AND group — always false
    $andContainer1 = RuleCondition::factory()->create([
        'rule_id' => $rule->getKey(),
        'type' => 'and_container',
        'parent_id' => $orContainer->getKey(),
        'position' => 0,
    ]);

    RuleCondition::factory()->create([
        'rule_id' => $rule->getKey(),
        'type' => 'always_false',
        'parent_id' => $andContainer1->getKey(),
    ]);

    // Second AND group — always true
    $andContainer2 = RuleCondition::factory()->create([
        'rule_id' => $rule->getKey(),
        'type' => 'and_container',
        'parent_id' => $orContainer->getKey(),
        'position' => 1,
    ]);

    RuleCondition::factory()->create([
        'rule_id' => $rule->getKey(),
        'type' => 'always_true',
        'parent_id' => $andContainer2->getKey(),
    ]);

    $result = RuleEvaluator::evaluate($rule, new PriceScope());
    expect($result)->toBeTrue();
});

test('and container returns false if any condition does not match', function (): void {
    $rule = Rule::factory()->create();

    $orContainer = RuleCondition::factory()->create([
        'rule_id' => $rule->getKey(),
        'type' => 'or_container',
        'parent_id' => null,
    ]);

    $andContainer = RuleCondition::factory()->create([
        'rule_id' => $rule->getKey(),
        'type' => 'and_container',
        'parent_id' => $orContainer->getKey(),
    ]);

    RuleCondition::factory()->create([
        'rule_id' => $rule->getKey(),
        'type' => 'always_true',
        'parent_id' => $andContainer->getKey(),
        'position' => 0,
    ]);

    RuleCondition::factory()->create([
        'rule_id' => $rule->getKey(),
        'type' => 'always_false',
        'parent_id' => $andContainer->getKey(),
        'position' => 1,
    ]);

    $result = RuleEvaluator::evaluate($rule, new PriceScope());
    expect($result)->toBeFalse();
});

test('inactive rule evaluates to false', function (): void {
    $rule = Rule::factory()->create(['is_active' => false]);
    $result = RuleEvaluator::evaluate($rule, new PriceScope());
    expect($result)->toBeFalse();
});
