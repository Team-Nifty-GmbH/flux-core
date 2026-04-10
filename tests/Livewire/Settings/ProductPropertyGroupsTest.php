<?php

use FluxErp\Livewire\Settings\ProductPropertyGroups;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(ProductPropertyGroups::class)
        ->assertOk();
});

test('edit with null resets form and opens modal', function (): void {
    Livewire::test(ProductPropertyGroups::class)
        ->call('edit', null)
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('productPropertyGroup.id', null)
        ->assertSet('productPropertyGroup.name', null)
        ->assertSet('productPropertyGroup.product_properties', [])
        ->assertOpensModal('edit-product-property-group-modal');
});
