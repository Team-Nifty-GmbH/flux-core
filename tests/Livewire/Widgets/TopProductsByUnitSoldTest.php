<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Widgets\TopProductsByUnitSold;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(TopProductsByUnitSold::class)
        ->assertStatus(200);
});
