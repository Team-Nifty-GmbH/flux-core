<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\DataTables\ActivityLogList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(ActivityLogList::class)
        ->assertStatus(200);
});
