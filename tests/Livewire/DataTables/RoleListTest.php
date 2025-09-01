<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\DataTables\RoleList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(RoleList::class)
        ->assertStatus(200);
});
