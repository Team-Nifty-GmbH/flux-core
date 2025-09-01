<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\DataTables\UnitList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(UnitList::class)
        ->assertStatus(200);
});
