<?php

uses(FluxErp\Tests\Livewire\BaseSetup::class);
use FluxErp\Livewire\Settings\Clients;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Clients::class)
        ->assertStatus(200);
});
