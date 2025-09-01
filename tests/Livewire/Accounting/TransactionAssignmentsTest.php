<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Accounting\TransactionAssignments;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(TransactionAssignments::class)
        ->assertStatus(200);
});
