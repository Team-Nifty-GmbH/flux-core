<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Widgets\RevenuePurchasesProfitChart;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(RevenuePurchasesProfitChart::class)
        ->assertStatus(200);
});
