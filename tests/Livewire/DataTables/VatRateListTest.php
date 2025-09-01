<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\DataTables\VatRateList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(VatRateList::class)
        ->assertStatus(200);
});
