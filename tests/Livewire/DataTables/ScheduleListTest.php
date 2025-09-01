<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\DataTables\ScheduleList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(ScheduleList::class)
        ->assertStatus(200);
});
