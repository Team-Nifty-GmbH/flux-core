<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Accounting\DirectDebit;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(DirectDebit::class)
        ->assertStatus(200);
});
