<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\ToggleDarkMode;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(ToggleDarkMode::class)
        ->assertStatus(200);
});
