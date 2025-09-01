<?php

uses(FluxErp\Tests\Livewire\BaseSetup::class);
use FluxErp\Livewire\Settings\AdditionalColumns;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(AdditionalColumns::class)
        ->assertStatus(200);
});
