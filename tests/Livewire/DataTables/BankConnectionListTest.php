<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\DataTables\BankConnectionList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(BankConnectionList::class)
        ->assertStatus(200);
});
