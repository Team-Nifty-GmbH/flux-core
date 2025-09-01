<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\DataTables\FailedJobList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(FailedJobList::class)
        ->assertStatus(200);
});
