<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\DataTables\SepaMandateList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(SepaMandateList::class)
        ->assertStatus(200);
});
