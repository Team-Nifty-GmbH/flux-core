<?php

uses(FluxErp\Tests\Livewire\BaseSetup::class);
use FluxErp\Livewire\Portal\Dashboard;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Dashboard::class)
        ->assertStatus(200);
});
