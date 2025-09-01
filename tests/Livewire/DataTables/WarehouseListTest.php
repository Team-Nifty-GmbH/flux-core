<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\DataTables\WarehouseList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(WarehouseList::class)
        ->assertStatus(200);
});
