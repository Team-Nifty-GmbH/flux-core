<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Settings\PaymentReminderTexts;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(PaymentReminderTexts::class)
        ->assertStatus(200);
});
