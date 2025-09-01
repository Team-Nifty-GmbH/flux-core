<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\DataTables\CommunicationList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(CommunicationList::class)
        ->assertStatus(200);
});
