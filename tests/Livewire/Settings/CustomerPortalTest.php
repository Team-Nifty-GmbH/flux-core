<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Settings\CustomerPortal;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(CustomerPortal::class)
        ->assertStatus(200);
});
