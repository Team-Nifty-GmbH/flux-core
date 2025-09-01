<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Product\MediaGrid;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(MediaGrid::class)
        ->assertStatus(200);
});
