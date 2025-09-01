<?php

uses(FluxErp\Tests\Livewire\BaseSetup::class);
use FluxErp\Livewire\DataTables\AdditionalColumnList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(AdditionalColumnList::class)
        ->assertStatus(200);
});
