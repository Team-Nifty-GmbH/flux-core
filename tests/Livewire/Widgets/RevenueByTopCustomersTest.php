<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Widgets\RevenueByTopCustomers;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(RevenueByTopCustomers::class)
        ->assertStatus(200);
});
