<?php

use FluxErp\Livewire\Resource\ResourceList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(ResourceList::class)
        ->assertOk();
});

test('the product search only offers products eligible for a resource', function (): void {
    Livewire::test(ResourceList::class)
        ->assertSeeHtml('is_bundle')
        ->assertSeeHtml('is_variant_parent');
});
