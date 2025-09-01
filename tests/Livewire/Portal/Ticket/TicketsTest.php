<?php

uses(FluxErp\Tests\Livewire\BaseSetup::class);
use FluxErp\Livewire\Portal\Ticket\Tickets;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Tickets::class)
        ->assertStatus(200);
});
