<?php

uses(FluxErp\Tests\Livewire\BaseSetup::class);
use FluxErp\Livewire\DataTables\CategoryList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(CategoryList::class)
        ->assertStatus(200);
});
