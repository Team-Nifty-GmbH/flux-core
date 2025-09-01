<?php

uses(FluxErp\Tests\Livewire\BaseSetup::class);
use FluxErp\Livewire\Settings\Languages;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Languages::class)
        ->assertStatus(200);
});
