<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\DataTables\WorkTimeTypeList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(WorkTimeTypeList::class)
        ->assertStatus(200);
});
