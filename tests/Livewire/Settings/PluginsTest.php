<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Settings\Plugins;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Plugins::class)
        ->assertStatus(200);
});
