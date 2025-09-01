<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Settings\PaymentTypes;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(PaymentTypes::class)
        ->assertStatus(200);
});
