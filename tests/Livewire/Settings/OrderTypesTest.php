<?php

uses(FluxErp\Tests\Livewire\BaseSetup::class);
use FluxErp\Livewire\Settings\OrderTypes;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(OrderTypes::class)
        ->assertStatus(200);
});
