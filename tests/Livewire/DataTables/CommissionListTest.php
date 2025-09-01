<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\DataTables\CommissionList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(CommissionList::class)
        ->assertStatus(200);
});
