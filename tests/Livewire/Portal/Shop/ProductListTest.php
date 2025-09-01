<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Portal\Shop\ProductList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(ProductList::class)
        ->assertStatus(200);
});
