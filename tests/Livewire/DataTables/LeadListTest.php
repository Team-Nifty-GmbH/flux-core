<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\DataTables\LeadList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(LeadList::class)
        ->assertStatus(200);
});
