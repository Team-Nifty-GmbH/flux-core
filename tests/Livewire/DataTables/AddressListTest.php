<?php

use FluxErp\Livewire\DataTables\AddressList;
use FluxErp\Models\Address;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(AddressList::class)
        ->assertOk();
});

test('preferred language id may be null', function (): void {
    $address = Address::factory()->make(['language_id' => null]);

    $method = new ReflectionMethod(AddressList::class, 'getPreferredLanguageId');

    expect($method->invoke(new AddressList(), $address))->toBeNull();
});
