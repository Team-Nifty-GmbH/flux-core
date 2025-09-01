<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\DataTables\OrderTransactionList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(OrderTransactionList::class)
        ->assertStatus(200);
});
