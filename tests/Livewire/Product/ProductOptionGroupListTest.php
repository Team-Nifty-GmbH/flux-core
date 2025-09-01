<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Product\ProductOptionGroupList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(ProductOptionGroupList::class)
        ->assertStatus(200);
});
