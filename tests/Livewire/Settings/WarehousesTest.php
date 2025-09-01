<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Settings\Warehouses;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Warehouses::class)
        ->assertStatus(200);
});
