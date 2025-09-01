<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\DataTables\QueueMonitorList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(QueueMonitorList::class)
        ->assertStatus(200);
});
