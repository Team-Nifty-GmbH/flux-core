<?php

use FluxErp\Enums\ConfigurationRuleTypeEnum;
use FluxErp\Livewire\Forms\ProductForm;
use FluxErp\Livewire\Product\ConfigurationGroups;
use FluxErp\Models\ProductConfigurationRule;
use Livewire\Livewire;

require_once __DIR__ . '/../../Feature/ProductConfiguration/Helpers.php';

beforeEach(function (): void {
    $this->product = configurationProduct(['is_configurable' => true]);
    $this->productForm = new ProductForm(Livewire::new(ConfigurationGroups::class), 'product');
    $this->productForm->fill($this->product);
    $this->group = configurationGroup(['name' => 'Storage']);
    $this->ssd = configurationItem($this->group, configurationProduct(['name' => 'SSD']), ['max_amount' => 2]);
});

test('renders successfully', function (): void {
    Livewire::test(ConfigurationGroups::class, ['product' => $this->productForm])->assertOk();
});

test('attach a group, mark it required and store a default', function (): void {
    $component = Livewire::test(ConfigurationGroups::class, ['product' => $this->productForm])
        ->set('groupId', $this->group->getKey())
        ->call('attach')
        ->assertReturned(true)
        ->assertSet('assignments.0.name', 'Storage')
        ->set('assignments.0.is_required', true)
        ->set('defaults.' . $this->ssd->getKey(), 2)
        ->call('save')
        ->assertReturned(true);

    expect((bool) $this->product->productConfigurationGroups()->first()->pivot->is_required)->toBeTrue()
        ->and($this->product->defaultConfiguration())
        ->toBe([['product_configuration_group_item_id' => $this->ssd->getKey(), 'amount' => 2]]);

    $component->call('detach', $component->get('assignments.0.pivot_id'))
        ->assertReturned(true)
        ->assertSet('assignments', []);
});

test('shows a warning for requires rules on groups that are not attached', function (): void {
    attachConfigurationGroup($this->product, $this->group);
    ProductConfigurationRule::factory()->create([
        'product_configuration_group_item_id' => $this->ssd->getKey(),
        'rule_type_enum' => ConfigurationRuleTypeEnum::Requires,
        'target_group_id' => configurationGroup()->getKey(),
    ]);

    Livewire::test(ConfigurationGroups::class, ['product' => $this->productForm])
        ->assertSet('warnings', ['SSD requires an option from a group that is not attached to this product.']);
});
