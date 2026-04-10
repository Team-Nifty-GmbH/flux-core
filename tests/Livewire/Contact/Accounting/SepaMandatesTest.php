<?php

use FluxErp\Livewire\Contact\Accounting\SepaMandates;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(SepaMandates::class)
        ->assertOk();
});

test('edit with null resets form and opens modal', function (): void {
    Livewire::test(SepaMandates::class)
        ->call('edit', null)
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('sepaMandate.id', null)
        ->assertSet('sepaMandate.contact_bank_connection_id', null)
        ->assertOpensModal('edit-sepa-mandate-modal');
});
