<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Settings\VatRates;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(VatRates::class)
        ->assertStatus(200);
});
