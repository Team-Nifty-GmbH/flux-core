<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Accounting\TransactionList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(TransactionList::class)
        ->assertStatus(200);
});
