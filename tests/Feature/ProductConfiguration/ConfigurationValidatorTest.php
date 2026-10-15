<?php

use FluxErp\Enums\ConfigurationRuleTypeEnum;
use FluxErp\Enums\ConfigurationSelectionTypeEnum;
use FluxErp\Models\ProductConfigurationRule;
use FluxErp\Support\ProductConfiguration\ConfigurationValidator;
use Illuminate\Validation\ValidationException;

require_once __DIR__ . '/Helpers.php';

beforeEach(function (): void {
    $this->product = configurationProduct(['is_configurable' => true]);
    $this->storage = configurationGroup(['name' => 'Storage']);
    $this->extras = configurationGroup([
        'name' => 'Extras',
        'selection_type_enum' => ConfigurationSelectionTypeEnum::Multiple,
        'min_selections' => 2,
        'max_selections' => 3,
    ]);
    $this->ssd = configurationItem($this->storage, configurationProduct(['name' => 'SSD']), ['max_amount' => 2]);
    $this->hdd = configurationItem($this->storage, configurationProduct(['name' => 'HDD']));
    $this->fan = configurationItem($this->extras, configurationProduct(['name' => 'Fan']));
    $this->led = configurationItem($this->extras, configurationProduct(['name' => 'LED']));
    $this->wifi = configurationItem($this->extras, configurationProduct(['name' => 'WiFi']));
    $this->dock = configurationItem($this->extras, configurationProduct(['name' => 'Dock']));
    attachConfigurationGroup($this->product, $this->storage, isRequired: true);
    attachConfigurationGroup($this->product, $this->extras);

    $this->validate = fn (array $configuration, bool $isDefault = false) => ConfigurationValidator::make($this->product)
        ->validate($configuration, $isDefault);
    $this->entry = fn ($item, int $amount = 1) => [
        'product_configuration_group_item_id' => $item->getKey(),
        'amount' => $amount,
    ];
    $this->messages = function (array $configuration, bool $isDefault = false): array {
        try {
            ($this->validate)($configuration, $isDefault);
        } catch (ValidationException $e) {
            return $e->errors()['configuration'];
        }

        return [];
    };
});

test('a valid configuration comes back normalized', function (): void {
    expect(($this->validate)([
        ($this->entry)($this->ssd, 2),
        ($this->entry)($this->fan),
        ($this->entry)($this->led),
    ]))->toBe([
        ['product_configuration_group_item_id' => $this->ssd->getKey(), 'amount' => 2],
        ['product_configuration_group_item_id' => $this->fan->getKey(), 'amount' => 1],
        ['product_configuration_group_item_id' => $this->led->getKey(), 'amount' => 1],
    ]);
});

test('check 1: options of groups not attached to the product are rejected', function (): void {
    $foreign = configurationItem(configurationGroup(), configurationProduct(['name' => 'Foreign']));

    expect(($this->messages)([($this->entry)($this->ssd), ($this->entry)($foreign)]))
        ->toBe(['Foreign does not belong to this product.']);
});

test('check 2: amount must stay between 1 and max_amount', function (): void {
    expect(($this->messages)([($this->entry)($this->ssd, 3)]))
        ->toBe(['SSD needs an amount between 1 and 2.'])
        ->and(($this->messages)([($this->entry)($this->ssd, 2)]))->toBe([]);
});

test('check 3: a single group takes at most one option', function (): void {
    expect(($this->messages)([($this->entry)($this->ssd), ($this->entry)($this->hdd)]))
        ->toBe(['Storage allows only one selection.']);
});

test('check 4: a multiple group respects min and max once something is chosen', function (): void {
    expect(($this->messages)([($this->entry)($this->ssd), ($this->entry)($this->fan)]))
        ->toBe(['Extras needs at least 2 selections.'])
        ->and(($this->messages)([
            ($this->entry)($this->ssd),
            ($this->entry)($this->fan),
            ($this->entry)($this->led),
            ($this->entry)($this->wifi),
            ($this->entry)($this->dock),
        ]))->toBe(['Extras allows at most 3 selections.'])
        ->and(($this->messages)([($this->entry)($this->ssd)]))->toBe([]);
});

test('check 5: a required group needs a selection, defaults may skip it', function (): void {
    expect(($this->messages)([]))->toBe(['Storage requires a selection.'])
        ->and(($this->messages)([], true))->toBe([]);
});

test('check 6: requires on a group', function (): void {
    ProductConfigurationRule::factory()->create([
        'product_configuration_group_item_id' => $this->ssd->getKey(),
        'rule_type_enum' => ConfigurationRuleTypeEnum::Requires,
        'target_group_id' => $this->extras->getKey(),
    ]);

    expect(($this->messages)([($this->entry)($this->ssd)]))
        ->toBe(['SSD requires a selection in Extras.'])
        ->and(($this->messages)([($this->entry)($this->ssd), ($this->entry)($this->fan), ($this->entry)($this->led)]))
        ->toBe([]);
});

test('check 6: excludes on a group', function (): void {
    ProductConfigurationRule::factory()->create([
        'product_configuration_group_item_id' => $this->hdd->getKey(),
        'rule_type_enum' => ConfigurationRuleTypeEnum::Excludes,
        'target_group_id' => $this->extras->getKey(),
    ]);

    expect(($this->messages)([($this->entry)($this->hdd), ($this->entry)($this->fan), ($this->entry)($this->led)]))
        ->toBe(['HDD cannot be combined with a selection in Extras.'])
        ->and(($this->messages)([($this->entry)($this->hdd)]))->toBe([]);
});

test('check 6: requires one of several options', function (): void {
    $rule = ProductConfigurationRule::factory()->create([
        'product_configuration_group_item_id' => $this->ssd->getKey(),
        'rule_type_enum' => ConfigurationRuleTypeEnum::Requires,
    ]);
    $rule->targetItems()->attach([$this->fan->getKey(), $this->wifi->getKey()]);

    expect(($this->messages)([($this->entry)($this->ssd), ($this->entry)($this->led), ($this->entry)($this->dock)]))
        ->toBe(['SSD requires one of Fan, WiFi.'])
        ->and(($this->messages)([($this->entry)($this->ssd), ($this->entry)($this->wifi), ($this->entry)($this->dock)]))
        ->toBe([]);
});

test('check 6: excludes options', function (): void {
    $rule = ProductConfigurationRule::factory()->create([
        'product_configuration_group_item_id' => $this->ssd->getKey(),
        'rule_type_enum' => ConfigurationRuleTypeEnum::Excludes,
    ]);
    $rule->targetItems()->attach([$this->led->getKey()]);

    expect(($this->messages)([($this->entry)($this->ssd), ($this->entry)($this->led), ($this->entry)($this->fan)]))
        ->toBe(['SSD cannot be combined with LED.'])
        ->and(($this->messages)([($this->entry)($this->ssd), ($this->entry)($this->fan), ($this->entry)($this->dock)]))
        ->toBe([]);
});

test('check 6: requires on a group that is not attached can never be met, excludes always passes', function (): void {
    $detached = configurationGroup(['name' => 'Cooling']);
    configurationItem($detached);
    ProductConfigurationRule::factory()->create([
        'product_configuration_group_item_id' => $this->ssd->getKey(),
        'rule_type_enum' => ConfigurationRuleTypeEnum::Requires,
        'target_group_id' => $detached->getKey(),
    ]);
    ProductConfigurationRule::factory()->create([
        'product_configuration_group_item_id' => $this->hdd->getKey(),
        'rule_type_enum' => ConfigurationRuleTypeEnum::Excludes,
        'target_group_id' => $detached->getKey(),
    ]);

    expect(($this->messages)([($this->entry)($this->ssd)]))->toBe(['SSD requires a selection in Cooling.'])
        ->and(($this->messages)([($this->entry)($this->hdd)]))->toBe([])
        ->and(ConfigurationValidator::make($this->product)->unsatisfiableRules())
        ->toBe(['SSD requires an option from a group that is not attached to this product.']);
});

test('check 7: deleted groups and deleted options are rejected', function (): void {
    $deletedItemId = $this->hdd->getKey();
    $this->hdd->delete();
    $trashedGroup = configurationGroup(['name' => 'Old']);
    $oldItem = configurationItem($trashedGroup);
    attachConfigurationGroup($this->product, $trashedGroup);
    $trashedGroup->delete();

    expect(($this->messages)([['product_configuration_group_item_id' => $deletedItemId, 'amount' => 1]]))
        ->toBe(['The selected option no longer exists.'])
        ->and(($this->messages)([($this->entry)($this->ssd), ($this->entry)($oldItem)]))
        ->toBe(['Old has been deleted.']);
});

test('rejects an option whose product was deleted', function (): void {
    $this->ssd->product->delete();

    expect(($this->messages)([($this->entry)($this->ssd)]))->toBe(['SSD is no longer available.']);
});

test('rejects the same option twice', function (): void {
    expect(($this->messages)([($this->entry)($this->ssd), ($this->entry)($this->ssd)]))
        ->toContain('An option was selected more than once.');
});
