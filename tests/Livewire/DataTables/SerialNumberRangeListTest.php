<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\DataTables\SerialNumberRangeList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(SerialNumberRangeList::class)
        ->assertStatus(200);
});
