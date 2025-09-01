<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\DataTables\WorkTimeList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(WorkTimeList::class)
        ->assertStatus(200);
});
