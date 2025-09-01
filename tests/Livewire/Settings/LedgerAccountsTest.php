<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Settings\LedgerAccounts;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(LedgerAccounts::class)
        ->assertStatus(200);
});
