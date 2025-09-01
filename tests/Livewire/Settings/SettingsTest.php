<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Settings\Settings;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Settings::class)
        ->assertStatus(200);
});
