<?php

uses(FluxErp\Tests\Livewire\BaseSetup::class);
use FluxErp\Livewire\DataTables\TransactionList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(TransactionList::class)
        ->assertStatus(200);
});
