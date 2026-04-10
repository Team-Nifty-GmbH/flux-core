<?php

use FluxErp\Livewire\Contact\Communication;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Communication::class)
        ->assertOk();
});

test('edit without arguments resets form and opens modal', function (): void {
    Livewire::test(Communication::class)
        ->call('edit')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('communication.id', null)
        ->assertSet('communication.subject', null)
        ->assertSet('communication.to', [])
        ->assertSet('communication.cc', [])
        ->assertSet('communication.bcc', [])
        ->assertOpensModal('edit-communication');
});

test('edit with null resets form and opens modal', function (): void {
    Livewire::test(Communication::class)
        ->call('edit', null)
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('communication.id', null)
        ->assertSet('communication.subject', null)
        ->assertOpensModal('edit-communication');
});
