<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Settings\Scheduling;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Scheduling::class)
        ->assertStatus(200);
});
