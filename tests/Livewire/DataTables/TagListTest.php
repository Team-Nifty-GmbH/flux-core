<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\DataTables\TagList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(TagList::class)
        ->assertStatus(200);
});
