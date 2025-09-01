<?php

uses(FluxErp\Tests\Livewire\BaseSetup::class);
use FluxErp\Livewire\DataTables\SerialNumberList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(SerialNumberList::class)
        ->assertStatus(200);
});
