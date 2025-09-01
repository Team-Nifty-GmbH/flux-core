<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Ticket\Communication;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Communication::class)
        ->assertStatus(200);
});
