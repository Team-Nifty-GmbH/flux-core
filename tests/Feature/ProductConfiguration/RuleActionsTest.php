<?php

use FluxErp\Actions\ProductConfigurationRule\CreateProductConfigurationRule;
use FluxErp\Actions\ProductConfigurationRule\DeleteProductConfigurationRule;
use FluxErp\Actions\ProductConfigurationRule\UpdateProductConfigurationRule;
use FluxErp\Enums\ConfigurationRuleTypeEnum;

require_once __DIR__ . '/Helpers.php';

beforeEach(function (): void {
    $this->graphics = configurationGroup(['name' => 'Graphics']);
    $this->power = configurationGroup(['name' => 'Power']);
    $this->gpu = configurationItem($this->graphics);
    $this->psu = configurationItem($this->power);
    $this->smallPsu = configurationItem($this->power);
});

test('create a rule on a whole group', function (): void {
    $rule = CreateProductConfigurationRule::make([
        'product_configuration_group_item_id' => $this->gpu->getKey(),
        'rule_type_enum' => 'requires',
        'target_group_id' => $this->power->getKey(),
    ])->validate()->execute();

    expect($rule->rule_type_enum)->toBe(ConfigurationRuleTypeEnum::Requires)
        ->and($rule->target_group_id)->toBe($this->power->getKey())
        ->and($rule->targetItems()->count())->toBe(0);
});

test('create a rule on options', function (): void {
    $rule = CreateProductConfigurationRule::make([
        'product_configuration_group_item_id' => $this->gpu->getKey(),
        'rule_type_enum' => 'excludes',
        'target_items' => [$this->smallPsu->getKey()],
    ])->validate()->execute();

    expect($rule->targetItems()->pluck('product_configuration_group_items.id')->all())
        ->toBe([$this->smallPsu->getKey()]);
});

test('a rule needs exactly one kind of target', function (): void {
    CreateProductConfigurationRule::assertValidationErrors([
        'product_configuration_group_item_id' => $this->gpu->getKey(),
        'rule_type_enum' => 'requires',
    ], ['target_group_id', 'target_items']);

    CreateProductConfigurationRule::assertValidationErrors([
        'product_configuration_group_item_id' => $this->gpu->getKey(),
        'rule_type_enum' => 'requires',
        'target_group_id' => $this->power->getKey(),
        'target_items' => [$this->psu->getKey()],
    ], 'target_items');
});

test('a rule cannot point at its own option or group', function (array $target): void {
    CreateProductConfigurationRule::assertValidationErrors(array_merge([
        'product_configuration_group_item_id' => $this->gpu->getKey(),
        'rule_type_enum' => 'excludes',
    ], $target === ['group'] ? ['target_group_id' => $this->graphics->getKey()] : ['target_items' => [$this->gpu->getKey()]]), 'target_group_id');
})->with([
    'own group' => [['group']],
    'own option' => [['option']],
]);

test('update switches a rule from options to a group', function (): void {
    $rule = CreateProductConfigurationRule::make([
        'product_configuration_group_item_id' => $this->gpu->getKey(),
        'rule_type_enum' => 'requires',
        'target_items' => [$this->psu->getKey()],
    ])->validate()->execute();

    $updated = UpdateProductConfigurationRule::make([
        'id' => $rule->getKey(),
        'target_group_id' => $this->power->getKey(),
    ])->validate()->execute();

    expect($updated->target_group_id)->toBe($this->power->getKey())
        ->and($updated->targetItems()->count())->toBe(0);
});

test('delete removes the rule and its targets', function (): void {
    $rule = CreateProductConfigurationRule::make([
        'product_configuration_group_item_id' => $this->gpu->getKey(),
        'rule_type_enum' => 'requires',
        'target_items' => [$this->psu->getKey()],
    ])->validate()->execute();

    DeleteProductConfigurationRule::make(['id' => $rule->getKey()])->validate()->execute();

    $this->assertDatabaseMissing('product_configuration_rules', ['id' => $rule->getKey()]);
    $this->assertDatabaseMissing('product_configuration_rule_items', [
        'product_configuration_rule_id' => $rule->getKey(),
    ]);
});
