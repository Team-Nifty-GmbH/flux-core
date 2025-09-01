<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\DataTables\PaymentReminderTextList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(PaymentReminderTextList::class)
        ->assertStatus(200);
});
