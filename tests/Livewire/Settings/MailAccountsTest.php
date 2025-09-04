<?php

use FluxErp\Livewire\Settings\MailAccounts;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(MailAccounts::class)
        ->assertOk();
});
