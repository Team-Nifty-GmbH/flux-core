<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\DataTables\IndustryList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(IndustryList::class)
        ->assertStatus(200);
});
