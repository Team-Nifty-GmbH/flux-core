<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\DataTables\ProductBundleProductList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(ProductBundleProductList::class)
        ->assertStatus(200);
});
