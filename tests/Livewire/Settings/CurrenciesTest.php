<?php

uses(FluxErp\Tests\Livewire\BaseSetup::class);
use FluxErp\Livewire\Settings\Currencies;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Currencies::class)
        ->assertStatus(200);
});
