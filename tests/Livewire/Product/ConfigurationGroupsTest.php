<?php

use FluxErp\Livewire\Forms\ProductForm;
use FluxErp\Livewire\Product\ConfigurationGroups;
use Livewire\Livewire;

require_once __DIR__ . '/../../Feature/ProductConfiguration/Helpers.php';

beforeEach(function (): void {
    $this->product = configurationProduct(['is_configurable' => true]);
    $this->productForm = new ProductForm(Livewire::new(ConfigurationGroups::class), 'product');
    $this->productForm->fill($this->product);
    $this->group = configurationGroup(['name' => 'Storage']);
});

test('renders successfully', function (): void {
    Livewire::test(ConfigurationGroups::class, ['product' => $this->productForm])->assertOk();
});

test('attach a group, mark it required and detach it', function (): void {
    $component = Livewire::test(ConfigurationGroups::class, ['product' => $this->productForm])
        ->set('groupId', $this->group->getKey())
        ->call('attach')
        ->assertReturned(true)
        ->assertSet('assignments.0.name', 'Storage')
        ->set('assignments.0.is_required', true)
        ->call('save')
        ->assertReturned(true);

    expect((bool) $this->product->productConfigurationGroups()->first()->pivot->is_required)->toBeTrue();

    $component->call('detach', $component->get('assignments.0.pivot_id'))
        ->assertReturned(true)
        ->assertSet('assignments', []);
});
