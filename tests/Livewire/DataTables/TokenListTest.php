<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\DataTables\TokenList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(TokenList::class)
        ->assertStatus(200);
});
