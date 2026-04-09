<?php

use FluxErp\Livewire\Contact\Accounting\SepaMandates;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(SepaMandates::class)
        ->assertOk();
});

test('open new modal', function (): void {
    Livewire::test(SepaMandates::class)
        ->call('edit', null)
        ->assertOk()
        ->assertHasNoErrors();
});
