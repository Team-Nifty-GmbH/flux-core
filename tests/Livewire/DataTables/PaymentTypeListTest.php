<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\DataTables\PaymentTypeList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(PaymentTypeList::class)
        ->assertStatus(200);
});
