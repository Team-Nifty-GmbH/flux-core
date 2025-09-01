<?php

uses(FluxErp\Tests\Livewire\BaseSetup::class);
use FluxErp\Livewire\DataTables\ClientList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(ClientList::class)
        ->assertStatus(200);
});
