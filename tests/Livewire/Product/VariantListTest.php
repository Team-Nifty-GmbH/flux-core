<?php

use FluxErp\Livewire\Forms\ProductForm;
use FluxErp\Livewire\Product\VariantList;
use FluxErp\Models\Product;
use FluxErp\Models\ProductOption;
use FluxErp\Models\ProductOptionGroup;
use FluxErp\Models\VatRate;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->vatRate = VatRate::factory()->create();
    $this->product = Product::factory()->create([
        'parent_id' => null,
        'vat_rate_id' => $this->vatRate->getKey(),
        'is_bundle' => false,
    ]);
    $this->product->tenants()->attach($this->dbTenant->getKey());

    $this->productForm = new ProductForm(Livewire::new(VariantList::class), 'product');
    $this->productForm->fill($this->product);
});

test('renders successfully', function (): void {
    Livewire::test(VariantList::class, ['product' => $this->productForm])
        ->assertOk();
});

test('load options returns options for group', function (): void {
    $group = ProductOptionGroup::factory()->create();
    $option = ProductOption::factory()->create([
        'product_option_group_id' => $group->getKey(),
        'name' => 'Option A',
    ]);

    $component = Livewire::test(VariantList::class, ['product' => $this->productForm])
        ->call('loadOptions', $group->getKey())
        ->assertOk()
        ->assertHasNoErrors();

    expect($component->get('productOptions'))->not->toBeEmpty();
    expect($component->get('productOptions.0.name'))->toEqual('Option A');
});

test('next generates variant plan', function (): void {
    $group = ProductOptionGroup::factory()->create();
    $optionA = ProductOption::factory()->create([
        'product_option_group_id' => $group->getKey(),
        'name' => 'Option A',
    ]);
    $optionB = ProductOption::factory()->create([
        'product_option_group_id' => $group->getKey(),
        'name' => 'Option B',
    ]);

    $component = Livewire::test(VariantList::class, ['product' => $this->productForm])
        ->set('selectedOptions.' . $group->getKey(), [$optionA->getKey(), $optionB->getKey()])
        ->call('next')
        ->assertOk()
        ->assertHasNoErrors();

    $variants = $component->get('variants');
    expect($variants)->toHaveKey('new');
    expect($variants['new'])->toHaveCount(2);
});

test('save creates new variants', function (): void {
    $group = ProductOptionGroup::factory()->create();
    $optionA = ProductOption::factory()->create([
        'product_option_group_id' => $group->getKey(),
        'name' => 'Red',
    ]);

    Livewire::test(VariantList::class, ['product' => $this->productForm])
        ->set('selectedOptions.' . $group->getKey(), [$optionA->getKey()])
        ->call('next')
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors();

    $children = Product::query()->where('parent_id', $this->product->getKey())->get();
    expect($children)->toHaveCount(1);
});

test('save deletes variants not in selection', function (): void {
    $group = ProductOptionGroup::factory()->create();
    $optionA = ProductOption::factory()->create([
        'product_option_group_id' => $group->getKey(),
        'name' => 'Red',
    ]);

    $child = Product::factory()->create([
        'parent_id' => $this->product->getKey(),
    ]);
    $child->tenants()->attach($this->dbTenant->getKey());
    $child->productOptions()->attach($optionA->getKey());

    Livewire::test(VariantList::class, ['product' => $this->productForm])
        ->set('selectedOptions.' . $group->getKey(), [])
        ->call('next')
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors();

    $this->assertSoftDeleted('products', [
        'id' => $child->getKey(),
    ]);
});

test('mount initializes selected options from existing children', function (): void {
    $group = ProductOptionGroup::factory()->create();
    $option = ProductOption::factory()->create([
        'product_option_group_id' => $group->getKey(),
        'name' => 'Blue',
    ]);

    $child = Product::factory()->create([
        'parent_id' => $this->product->getKey(),
    ]);
    $child->productOptions()->attach($option->getKey());

    $component = Livewire::test(VariantList::class, ['product' => $this->productForm]);

    $selectedOptions = $component->get('selectedOptions');
    expect($selectedOptions[$group->getKey()])->toContain($option->getKey());
});
