<?php

uses(FluxErp\Tests\Livewire\BaseSetup::class);
use FluxErp\Livewire\DataTables\ContactList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(ContactList::class)
        ->assertStatus(200);
});
