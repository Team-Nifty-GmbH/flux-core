<?php

uses(FluxErp\Tests\Livewire\PortalBaseSetup::class);
use FluxErp\Livewire\Portal\Ticket\Tickets;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Tickets::class)
        ->assertStatus(200);
});
