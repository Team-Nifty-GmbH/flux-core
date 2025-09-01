<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Widgets\Generic;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Generic::class)
        ->assertStatus(200);
});
