<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Widgets\OpenDeliveries;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(OpenDeliveries::class)
        ->assertStatus(200);
});
