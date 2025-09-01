<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Accounting\OrderList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(OrderList::class)
        ->assertStatus(200);
});
