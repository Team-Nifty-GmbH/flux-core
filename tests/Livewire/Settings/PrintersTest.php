<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Settings\Printers;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Printers::class)
        ->assertStatus(200);
});
