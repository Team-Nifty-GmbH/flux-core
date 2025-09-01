<?php

uses(FluxErp\Tests\Livewire\BaseSetup::class);
use FluxErp\Livewire\DataTables\LogList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(LogList::class)
        ->assertStatus(200);
});
