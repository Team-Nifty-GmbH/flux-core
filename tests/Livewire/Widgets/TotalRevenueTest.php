<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Widgets\TotalRevenue;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(TotalRevenue::class)
        ->assertStatus(200);
});
