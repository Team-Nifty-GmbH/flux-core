<?php

use FluxErp\Livewire\Contact\Communication;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Communication::class)
        ->assertOk();
});

test('can call edit without arguments to create new communication', function (): void {
    Livewire::test(Communication::class)
        ->call('edit')
        ->assertOk();
});

test('can call edit with null to create a new communication', function (): void {
    Livewire::test(Communication::class)
        ->call('edit', null)
        ->assertOk();
});
