<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Lead\Orders;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Orders::class)
        ->assertStatus(200);
});
