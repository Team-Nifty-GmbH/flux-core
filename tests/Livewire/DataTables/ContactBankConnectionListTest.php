<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\DataTables\ContactBankConnectionList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(ContactBankConnectionList::class)
        ->assertStatus(200);
});
