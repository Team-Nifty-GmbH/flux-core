<?php

uses(FluxErp\Tests\Livewire\BaseSetup::class);
use FluxErp\Livewire\Settings\DiscountGroups;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(DiscountGroups::class)
        ->assertStatus(200);
});
