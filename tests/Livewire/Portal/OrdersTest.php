<?php

uses(FluxErp\Tests\Livewire\BaseSetup::class);
use FluxErp\Livewire\Portal\Orders;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Orders::class)
        ->assertStatus(200);
});
