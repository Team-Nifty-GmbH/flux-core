<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Widgets\TotalOrdersCount;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(TotalOrdersCount::class)
        ->assertStatus(200);
});
