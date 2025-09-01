<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\DataTables\AddressTypeList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(AddressTypeList::class)
        ->assertStatus(200);
});
