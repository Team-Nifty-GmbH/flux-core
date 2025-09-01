<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Accounting\MoneyTransfer;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(MoneyTransfer::class)
        ->assertStatus(200);
});
