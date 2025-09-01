<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Settings\AddressTypes;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(AddressTypes::class)
        ->assertStatus(200);
});
