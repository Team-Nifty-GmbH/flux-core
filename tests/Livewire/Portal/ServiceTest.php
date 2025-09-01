<?php

uses(FluxErp\Tests\Livewire\PortalBaseSetup::class);
use FluxErp\Livewire\Portal\Service;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Service::class)
        ->assertStatus(200);
});
