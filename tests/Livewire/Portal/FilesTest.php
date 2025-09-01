<?php

uses(FluxErp\Tests\Livewire\BaseSetup::class);
use FluxErp\Livewire\Portal\Files;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Files::class)
        ->assertStatus(200);
});
