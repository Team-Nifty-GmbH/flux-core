<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Settings\MailAccounts;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(MailAccounts::class)
        ->assertStatus(200);
});
