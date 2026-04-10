<?php

use FluxErp\Livewire\Settings\ProductOptionGroups;
use FluxErp\Models\ProductOptionGroup;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(ProductOptionGroups::class)
        ->assertOk();
});

test('edit with null resets form and opens modal', function (): void {
    Livewire::test(ProductOptionGroups::class)
        ->call('edit', null)
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('productOptionGroupForm.id', null)
        ->assertSet('productOptionGroupForm.name', null)
        ->assertSet('productOptionGroupForm.product_options', [])
        ->assertOpensModal('edit-product-option-group-modal');
});

test('edit with model fills form and opens modal', function (): void {
    $group = ProductOptionGroup::factory()->create();

    Livewire::test(ProductOptionGroups::class)
        ->call('edit', $group->getKey())
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('productOptionGroupForm.id', $group->getKey())
        ->assertSet('productOptionGroupForm.name', $group->name)
        ->assertOpensModal('edit-product-option-group-modal');
});
