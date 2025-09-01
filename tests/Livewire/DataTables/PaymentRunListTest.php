<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\DataTables\PaymentRunList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(PaymentRunList::class)
        ->assertStatus(200);
});
