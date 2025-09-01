<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Portal\Order\ProductMedia;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(ProductMedia::class)
        ->assertStatus(200);
});
