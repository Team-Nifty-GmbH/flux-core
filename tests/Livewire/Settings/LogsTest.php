<?php

uses(FluxErp\Tests\Livewire\BaseSetup::class);
use FluxErp\Livewire\Settings\Logs;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Logs::class)
        ->assertStatus(200);
});
