<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Widgets\AverageOrderValue;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(AverageOrderValue::class)
        ->assertStatus(200);
});
