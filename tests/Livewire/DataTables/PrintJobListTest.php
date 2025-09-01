<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\DataTables\PrintJobList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(PrintJobList::class)
        ->assertStatus(200);
});
