<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\DataTables\ProductPropertyGroupList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(ProductPropertyGroupList::class)
        ->assertStatus(200);
});
