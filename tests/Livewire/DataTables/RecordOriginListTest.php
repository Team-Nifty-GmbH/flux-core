<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\DataTables\RecordOriginList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(RecordOriginList::class)
        ->assertStatus(200);
});
