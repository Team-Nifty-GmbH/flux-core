<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\DataTables\LeadStateList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(LeadStateList::class)
        ->assertStatus(200);
});
