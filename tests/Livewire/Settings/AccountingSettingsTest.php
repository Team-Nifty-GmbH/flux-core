<?php

use FluxErp\Livewire\Settings\AccountingSettings;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(AccountingSettings::class)
        ->assertOk();
});
