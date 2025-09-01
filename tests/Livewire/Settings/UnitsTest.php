<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Settings\Units;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Units::class)
        ->assertStatus(200);
});
