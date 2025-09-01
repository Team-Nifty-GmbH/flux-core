<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Contact\Accounting\BankConnections;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(BankConnections::class)
        ->assertStatus(200);
});
